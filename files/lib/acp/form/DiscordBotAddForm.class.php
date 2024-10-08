<?php

namespace wcf\acp\form;

use Override;
use wcf\data\discord\bot\DiscordBotAction;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\discord\DiscordApi;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\FileProcessorFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\PasswordFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;

class DiscordBotAddForm extends AbstractFormBuilderForm
{
    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.discord.canManageConnection'];

    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.configuration.discord.discordBotList.add';

    /**
     * @inheritDoc
     */
    public $objectActionClass = DiscordBotAction::class;

    /**
     * Name des Discord-Servers
     *
     * @var string
     */
    protected $guildName;

    /**
     * Hash des Server-Icons
     *
     * @var string
     */
    protected $guildIcon;

    #[Override]
    protected function createForm()
    {
        parent::createForm();

        $this->form->appendChildren([
            FormContainer::create('botSettings')
                ->label('wcf.acp.discordBotAdd.botSettings')
                ->appendChildren([
                    TextFormField::create('botName')
                        ->label('wcf.acp.discordBotList.botName')
                        ->description('wcf.acp.discordBotAdd.botName.description')
                        ->required()
                        ->maximumLength(50),
                    PasswordFormField::create('botToken')
                        ->label('wcf.acp.discordBotAdd.botToken')
                        ->required(($this->formAction == 'create'))
                        ->addFieldClass('long')
                        ->removeFieldClass('medium')
                        ->placeholder(
                            ($this->formAction == 'edit') ? 'wcf.acp.updateServer.loginPassword.noChange' : ''
                        ),
                    IntegerFormField::create('guildID')
                        ->label('wcf.acp.discordBotAdd.guildID')
                        ->description('wcf.acp.discordBotAdd.guildID.description')
                        ->required()
                        ->addValidator(new FormFieldValidator('guildIDCheck', function (IntegerFormField $formField) {
                            $requestData = $this->form->getRequestData();
                            $botToken = $requestData['botToken'];

                            if ($this->formAction == 'edit' && $botToken === '') {
                                $botToken = $this->formObject->botToken;
                            }

                            $discord = new DiscordApi($formField->getValue(), $botToken);
                            $guild = $discord->getGuild();
                            if ($guild['status'] == 0) {
                                $formField->addValidationError(new FormFieldValidationError(
                                    'noConnection',
                                    'wcf.acp.discordBotAdd.guildID.error.noConnection'
                                ));
                            } elseif ($guild['status'] != 200) {
                                $formField->addValidationError(new FormFieldValidationError(
                                    'permissionDenied',
                                    'wcf.acp.discordBotAdd.guildID.error.permissionDenied'
                                ));
                            }

                            if (isset($guild['body']['name']) && $guild['body']['name'] !== '') {
                                $this->guildName = $guild['body']['name'];
                            }
                            if (isset($guild['body']['icon']) && $guild['body']['icon'] !== '') {
                                $this->guildIcon = $guild['body']['icon'];
                            }
                        })),
                ]),
            FormContainer::create('webhookSettings')
                ->label('wcf.acp.discordBotAdd.webhookSettings')
                ->appendChildren([
                    TextFormField::create('webhookName')
                        ->label('wcf.acp.discordBotAdd.webhookName')
                        ->description('wcf.acp.discordBotAdd.webhookName.description')
                        ->required()
                        ->maximumLength(50)
                        ->value(PAGE_TITLE),
                    FileProcessorFormField::create('webhookIconID')
                        ->objectType('dev.hanashi.wsc.discord.webhook.avatar')
                        ->label('wcf.acp.discordBotAdd.webhookIcon')
                        ->description('wcf.acp.discordBotAdd.webhookIcon.description')
                        ->singleFileUpload()
                        ->bigPreview(),
                ]),
            FormContainer::create('oauth2Settings')
                ->label('wcf.acp.discordBotAdd.oauth2Settings')
                ->appendChildren([
                    IntegerFormField::create('clientID')
                        ->label('wcf.acp.discordBotAdd.clientID')
                        ->description('wcf.acp.discordBotAdd.clientID.description'),
                    PasswordFormField::create('clientSecret')
                        ->label('wcf.acp.discordBotAdd.clientSecret')
                        ->addFieldClass('long')
                        ->removeFieldClass('medium')
                        ->placeholder(
                            ($this->formAction == 'edit') ? 'wcf.acp.updateServer.loginPassword.noChange' : ''
                        ),
                ]),
            FormContainer::create('interaction')
                ->label('wcf.acp.discordBotAdd.interaction')
                ->appendChildren([
                    TextFormField::create('publicKey')
                        ->label('wcf.acp.discordBotAdd.publicKey')
                        ->description('wcf.acp.discordBotAdd.publicKey.description'),
                ]),
        ]);
    }

    #[Override]
    public function save()
    {
        $additionalFields = [
            'guildName' => $this->guildName,
            'guildIcon' => $this->guildIcon,
        ];

        if ($this->formAction == 'create') {
            $additionalFields['botTime'] = TIME_NOW;
        }

        $this->additionalFields = \array_merge($this->additionalFields, $additionalFields);

        parent::save();
    }
}
