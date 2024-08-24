<?php

namespace wcf\acp\form;

use Override;
use wcf\data\discord\bot\DiscordBotAction;
use wcf\event\discord\DiscordOAuthRequiredCollecting;
use wcf\event\discord\DiscordPublicKeyRequiredCollecting;
use wcf\form\AbstractForm;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\cache\builder\DiscordCurrentGuildsCacheBuilder;
use wcf\system\discord\DiscordApi;
use wcf\system\event\EventHandler;
use wcf\system\form\builder\button\FormButton;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\dependency\NonEmptyFormFieldDependency;
use wcf\system\form\builder\field\HiddenFormField;
use wcf\system\form\builder\field\PasswordFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

class DiscordBotAddManagerForm extends AbstractFormBuilderForm
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
     * current step of bot add manager
     */
    protected int $step = 0;

    /**
     * cached bot data
     */
    protected array $botData = [];

    /**
     * temp information
     *
     * @var array
     */
    protected $tempInfo;

    /**
     * list of guilds
     */
    protected array $guilds = [];

    #[Override]
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_GET['step'])) {
            $this->step = (int)$_GET['step'];
        }
    }

    #[Override]
    protected function createForm()
    {
        parent::createForm();

        switch ($this->step) {
            case 1:
                $this->createFormStep1();
                break;
            case 2:
                $this->createFormStep2();
                break;
            case 3:
                $this->createFormStep3();
                break;
            case 4:
                $this->createFormStep4();
                break;
            case 5:
                $this->createFormStep5();
                break;
        }

        $this->form->addDefaultButton(false);
        $this->form->addButton(
            FormButton::create('next')
                ->label('wcf.acp.discordBotAddManager.next')
                ->submit(true)
        );
    }

    protected function createFormStep1()
    {
        $this->form->appendChildren([
            FormContainer::create('data')
                ->appendChildren([
                    PasswordFormField::create('botToken')
                        ->label('wcf.acp.discordBotAdd.botToken')
                        ->description('wcf.acp.discordBotAddManager.botToken.description')
                        ->addFieldClass('long')
                        ->removeFieldClass('medium')
                        ->required(true)
                        ->addValidator(new FormFieldValidator('tokenCheck', function (PasswordFormField $formField) {
                            $botToken = $formField->getValue();

                            $discord = new DiscordApi(0, $botToken);
                            $bot = $discord->getCurrentUser();
                            if (!isset($bot['body']['id'])) {
                                $formField->addValidationError(new FormFieldValidationError(
                                    'invalidBotToken',
                                    'wcf.acp.discordBotAddManager.botToken.invalid'
                                ));
                            } else {
                                $this->tempInfo = $bot['body'];
                            }
                        })),
                ]),
        ]);
    }

    protected function createFormStep2()
    {
        $requestData = $this->form->getRequestData();
        $this->form->appendChildren([
            FormContainer::create('data')
                ->appendChildren([
                    HiddenFormField::create('botToken')
                        ->value($requestData['botToken'])
                        ->required(),
                    HiddenFormField::create('clientID')
                        ->value($requestData['clientID'] ?? $this->tempInfo['id'])
                        ->required(),
                    HiddenFormField::create('botName')
                        ->value(
                            $requestData['botName'] ?? $this->tempInfo['username'] . '#'
                                                       . $this->tempInfo['discriminator']
                        )
                        ->required(),
                ]),
        ]);
    }

    protected function createFormStep3()
    {
        $requestData = $this->form->getRequestData();
        $this->form->appendChildren([
            FormContainer::create('data')
                ->appendChildren([
                    SingleSelectionFormField::create('guildID')
                        ->label('wcf.acp.discordBotAddManager.guildID')
                        ->options(
                            DiscordCurrentGuildsCacheBuilder::getInstance()->getData(
                                [
                                    'botToken' => $requestData['botToken'],
                                ]
                            )
                        )
                        ->filterable()
                        ->required()
                        ->addValidator(
                            new FormFieldValidator(
                                'guildCheck',
                                function (SingleSelectionFormField $formField) {
                                    $guildID = $formField->getValue();

                                    $requestData = $this->form->getRequestData();
                                    $discord = new DiscordApi($guildID, $requestData['botToken']);
                                    $guild = $discord->getGuild();
                                    if (!isset($guild['body']['id'])) {
                                        $formField->addValidationError(new FormFieldValidationError(
                                            'invalidGuild',
                                            'wcf.acp.discordBotAddManager.guildID.invalid'
                                        ));
                                    } else {
                                        $this->tempInfo = $guild['body'];
                                    }
                                }
                            )
                        ),
                    HiddenFormField::create('botToken')
                        ->value($requestData['botToken'])
                        ->required(),
                    HiddenFormField::create('clientID')
                        ->value($requestData['clientID'])
                        ->required(),
                    HiddenFormField::create('botName')
                        ->value($requestData['botName'])
                        ->required(),
                ]),
        ]);
    }

    protected function createFormStep4()
    {
        $oauthRequiredCollecting = new DiscordOAuthRequiredCollecting();
        EventHandler::getInstance()->fire($oauthRequiredCollecting);

        $requestData = $this->form->getRequestData();
        $this->form->appendChildren([
            FormContainer::create('data')
                ->appendChildren([
                    BooleanFormField::create('useOAuth2')
                        ->label('wcf.acp.discordBotAddManager.useOAuth2')
                        ->required($oauthRequiredCollecting->needOauth2())
                        ->value($oauthRequiredCollecting->needOauth2()),
                    PasswordFormField::create('clientSecret')
                        ->label('wcf.acp.discordBotAddManager.clientSecret')
                        ->addFieldClass('long')
                        ->removeFieldClass('medium')
                        ->required()
                        ->addDependency(
                            NonEmptyFormFieldDependency::create('isUsingOauth2')
                                ->fieldId('useOAuth2')
                        ),
                    HiddenFormField::create('botToken')
                        ->value($requestData['botToken'])
                        ->required(),
                    HiddenFormField::create('clientID')
                        ->value($requestData['clientID'])
                        ->required(),
                    HiddenFormField::create('botName')
                        ->value($requestData['botName'])
                        ->required(),
                    HiddenFormField::create('guildID')
                        ->value($requestData['guildID'])
                        ->required(),
                    HiddenFormField::create('guildName')
                        ->value($requestData['guildName'] ?? $this->tempInfo['name']),
                    HiddenFormField::create('guildIcon')
                        ->value($requestData['guildIcon'] ?? $this->tempInfo['icon']),
                ]),
        ]);
    }

    protected function createFormStep5()
    {
        $publicKeyRequiredCollecting = new DiscordPublicKeyRequiredCollecting();
        EventHandler::getInstance()->fire($publicKeyRequiredCollecting);

        $requestData = $this->form->getRequestData();
        $this->form->appendChildren([
            FormContainer::create('data')
                ->appendChildren([
                    BooleanFormField::create('useApplicationCommands')
                        ->label('wcf.acp.discordBotAddManager.useApplicationCommands')
                        ->required($publicKeyRequiredCollecting->needPublicKey())
                        ->value($publicKeyRequiredCollecting->needPublicKey()),
                    TextFormField::create('publicKey')
                        ->label('wcf.acp.discordBotAddManager.publicKey')
                        ->required()
                        ->addDependency(
                            NonEmptyFormFieldDependency::create('isUsingApplicationCommand')
                                ->fieldId('useApplicationCommands')
                        ),
                    HiddenFormField::create('botToken')
                        ->value($requestData['botToken'])
                        ->required(),
                    HiddenFormField::create('clientID')
                        ->value($requestData['clientID'])
                        ->required(),
                    HiddenFormField::create('botName')
                        ->value($requestData['botName'])
                        ->required(),
                    HiddenFormField::create('guildID')
                        ->value($requestData['guildID'])
                        ->required(),
                    HiddenFormField::create('guildName')
                        ->value($requestData['guildName']),
                    HiddenFormField::create('guildIcon')
                        ->value($requestData['guildIcon']),
                    HiddenFormField::create('clientSecret')
                        ->value($requestData['clientSecret'] ?? ''),
                ]),
        ]);
    }

    #[Override]
    protected function setFormAction()
    {
        $this->form->action(LinkHandler::getInstance()->getControllerLink(static::class, ['step' => $this->step]));
    }

    #[Override]
    public function save()
    {
        $this->step++;
        if ($this->step == 6) {
            $this->additionalFields['botTime'] = \TIME_NOW;
            $this->additionalFields['webhookName'] = \PAGE_TITLE;

            parent::save();
        } else {
            AbstractForm::save();

            $this->saved();
            $this->form->showSuccessMessage(false);
        }
    }

    #[Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'step' => $this->step,
            'tempInfo' => $this->tempInfo,
        ]);
    }
}
