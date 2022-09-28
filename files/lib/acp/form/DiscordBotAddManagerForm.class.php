<?php

namespace wcf\acp\form;

use wcf\data\discord\bot\DiscordBotAction;
use wcf\data\package\PackageCache;
use wcf\form\AbstractForm;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\discord\DiscordApi;
use wcf\system\discord\SecretFormField;
use wcf\system\form\builder\button\FormButton;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\dependency\NonEmptyFormFieldDependency;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

class DiscordBotAddManagerForm extends AbstractFormBuilderForm
{
    private const SESSION_VAR = self::class . "\0botData";

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
     *
     * @var int
     */
    protected $step = 0;

    /**
     * cached bot data
     *
     * @var array
     */
    protected $botData = [];

    /**
     * object of discord api
     *
     * @var DiscordApi
     */
    protected $discord;

    /**
     * botInfo
     *
     * @var array
     */
    protected $botInfo;

    /**
     * list of guilds
     *
     * @var array
     */
    protected $guilds = [];

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_GET['step'])) {
            $this->step = (int)$_GET['step'];
        }

        if ($this->step <= 1 || $this->step == 6) {
            if (!empty(WCF::getSession()->getVar(self::SESSION_VAR))) {
                WCF::getSession()->unregister(self::SESSION_VAR);
            }

            return;
        }

        $this->additionalFields = WCF::getSession()->getVar(self::SESSION_VAR);
        if (
            $this->step > 1
            && (
                empty($this->additionalFields['botToken'])
                || empty($this->additionalFields['clientID'])
            )
        ) {
            HeaderUtil::redirect(LinkHandler::getInstance()->getControllerLink(self::class, ['step' => 1]));

            exit;
        }

        $guildID = $this->getGuildID();
        $this->discord = new DiscordApi($guildID, $this->additionalFields['botToken']);
        if ($this->step === 3) {
            $this->getBotGuilds();
        }
    }

    protected function getGuildID()
    {
        $guildID = 0;
        if ($this->step > 3 && empty($this->additionalFields['guildID'])) {
            HeaderUtil::redirect(LinkHandler::getInstance()->getControllerLink(self::class, ['step' => 3]));

            exit;
        }
        if (!empty($this->additionalFields['guildID'])) {
            $guildID = (int)$this->additionalFields['guildID'];
        }

        return $guildID;
    }

    protected function getBotGuilds()
    {
        $currentUserGuilds = $this->discord->getCurrentUserGuilds();
        if (!isset($currentUserGuilds['body']) || \count($currentUserGuilds['body']) && empty($currentUserGuilds['body'][0])) {
            HeaderUtil::redirect(LinkHandler::getInstance()->getControllerLink(self::class, ['step' => 1]));

            exit;
        }
        $guilds = $currentUserGuilds['body'];
        \usort($guilds, static fn ($a, $b) => \strtoupper($a['name']) <=> \strtoupper($b['name']));

        foreach ($guilds as $guild) {
            $this->guilds[$guild['id']] = $guild['name'];
        }
    }

    /**
     * @inheritDoc
     */
    protected function createForm()
    {
        parent::createForm();

        switch ($this->step) {
            case 1:
                $this->createFormStep1();
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
                    SecretFormField::create('botToken')
                        ->label('wcf.acp.discordBotAdd.botToken')
                        ->description('wcf.acp.discordBotAddManager.botToken.description')
                        ->required(true)
                        ->addValidator(new FormFieldValidator('tokenCheck', function (SecretFormField $formField) {
                            $botToken = $formField->getValue();

                            $discord = new DiscordApi(0, $botToken);
                            $bot = $discord->getCurrentUser();
                            if (empty($bot['body']['id'])) {
                                $formField->addValidationError(new FormFieldValidationError(
                                    'invalidBotToken',
                                    'wcf.acp.discordBotAddManager.botToken.invalid'
                                ));
                            } else {
                                $this->botInfo = $bot['body'];
                            }
                        })),
                ]),
        ]);
    }

    protected function createFormStep3()
    {
        $this->form->appendChildren([
            FormContainer::create('data')
                ->appendChildren([
                    SingleSelectionFormField::create('guildID')
                        ->label('wcf.acp.discordBotAddManager.guildID')
                        ->options($this->guilds)
                        ->filterable()
                        ->required()
                        ->addValidator(new FormFieldValidator('guildCheck', function (SingleSelectionFormField $formField) {
                            $guildID = $formField->getValue();

                            $discord = new DiscordApi($guildID, $this->additionalFields['botToken']);
                            $guild = $discord->getGuild();
                            if (empty($guild['body']['id'])) {
                                $formField->addValidationError(new FormFieldValidationError(
                                    'invalidGuild',
                                    'wcf.acp.discordBotAddManager.guildID.invalid'
                                ));
                            } else {
                                $this->additionalFields['guildName'] = $guild['body']['name'];
                                $this->additionalFields['guildIcon'] = $guild['body']['icon'];
                            }
                        })),
                ]),
        ]);
    }

    protected function createFormStep4()
    {
        $this->form->appendChildren([
            FormContainer::create('data')
                ->appendChildren([
                    BooleanFormField::create('useOAuth2')
                        ->label('wcf.acp.discordBotAddManager.useOAuth2')
                        ->description('wcf.acp.discordBotAddManager.useOAuth2.description')
                        ->required($this->isDiscordSyncInstalled())
                        ->value($this->isDiscordSyncInstalled()),
                    SecretFormField::create('clientSecret')
                        ->label('wcf.acp.discordBotAddManager.clientSecret')
                        ->required()
                        ->addDependency(
                            NonEmptyFormFieldDependency::create('isUsingOauth2')
                                ->fieldId('useOAuth2')
                        ),
                ]),
        ]);
    }

    protected function createFormStep5()
    {
        $this->form->appendChildren([
            FormContainer::create('data')
                ->appendChildren([
                    BooleanFormField::create('useApplicationCommands')
                        ->label('wcf.acp.discordBotAddManager.useApplicationCommands'),
                    TextFormField::create('publicKey')
                        ->label('wcf.acp.discordBotAddManager.publicKey')
                        ->required()
                        ->addDependency(
                            NonEmptyFormFieldDependency::create('isUsingApplicationCommand')
                                ->fieldId('useApplicationCommands')
                        ),
                ]),
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function setFormAction()
    {
        $this->form->action(LinkHandler::getInstance()->getControllerLink(static::class, ['step' => $this->step]));
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        if ($this->step != 5) {
            AbstractForm::save();
        }

        switch ($this->step) {
            case 1:
                $this->saveStep1();
                break;
            case 3:
                $this->saveStep3();
                break;
            case 4:
                $this->saveStep4();
                break;
            case 5:
                $this->saveStep5();
        }

        if ($this->step == 5) {
            parent::save();
        } else {
            $this->saved();
        }
        $this->redirectToNextStep();
    }

    protected function saveStep1()
    {
        $formData = $this->form->getData();
        $botToken = $formData['data']['botToken'];
        $this->additionalFields['botToken'] = $botToken;
        $this->additionalFields['clientID'] = $this->botInfo['id'];
        $this->additionalFields['botName'] = $this->botInfo['username'] . '#' . $this->botInfo['discriminator'];
        WCF::getSession()->register(self::SESSION_VAR, $this->additionalFields);
    }

    protected function saveStep3()
    {
        $formData = $this->form->getData();
        $this->additionalFields['guildID'] = $formData['data']['guildID'];
        WCF::getSession()->register(self::SESSION_VAR, $this->additionalFields);
    }

    protected function saveStep4()
    {
        $formData = $this->form->getData();
        if (!empty($formData['data']['clientSecret'])) {
            $this->additionalFields['clientSecret'] = $formData['data']['clientSecret'];
        }
        WCF::getSession()->register(self::SESSION_VAR, $this->additionalFields);
    }

    protected function saveStep5()
    {
        $this->additionalFields['botTime'] = \TIME_NOW;
        $this->additionalFields['webhookName'] = \PAGE_TITLE;
        WCF::getSession()->unregister(self::SESSION_VAR);
    }

    protected function redirectToNextStep()
    {
        HeaderUtil::redirect(LinkHandler::getInstance()->getControllerLink(self::class, ['step' => $this->step + 1]));

        exit;
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'step' => $this->step,
            'botData' => $this->additionalFields,
            'discordSyncInstalled' => $this->isDiscordSyncInstalled(),
        ]);
    }

    private function isDiscordSyncInstalled()
    {
        $package = PackageCache::getInstance()->getPackageByIdentifier('eu.hanashi.discord-sync');

        return $package !== null;
    }
}
