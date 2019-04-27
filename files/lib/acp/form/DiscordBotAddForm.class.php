<?php
namespace wcf\acp\form;
use wcf\data\discord\bot\DiscordBotAction;
use wcf\form\AbstractForm;
use wcf\system\discord\DiscordApi;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\StringUtil;

class DiscordBotAddForm extends AbstractForm {
    public $neededPermissions = ['admin.discord.canManageConnection'];

    public $activeMenuItem = 'wcf.acp.menu.link.configuration.discord.discordBotList.add';

    protected $discordBotID;

    protected $botName = 'Default';

    protected $botToken;

    protected $guildID;

    protected $webhookName = PAGE_TITLE;

    protected $clientID;

    protected $clientSecret;

    protected $guildName;

    protected $guildIcon;

    public function readFormParameters() {
        parent::readFormParameters();

        if (isset($_POST['botName'])) $this->botName = StringUtil::trim($_POST['botName']);
        if (isset($_POST['botToken'])) $this->botToken = StringUtil::trim($_POST['botToken']);
        if (isset($_POST['guildID'])) $this->guildID = StringUtil::trim($_POST['guildID']);
        if (isset($_POST['webhookName'])) $this->webhookName = StringUtil::trim($_POST['webhookName']);
        if (isset($_POST['clientID'])) $this->clientID = StringUtil::trim($_POST['clientID']);
        if (isset($_POST['clientSecret'])) $this->clientSecret = StringUtil::trim($_POST['clientSecret']);
    }

    public function validate() {
        parent::validate();

        // TODO: lang
        if (empty($this->botName)) {
            throw new UserInputException('botName');
        }
        if (strlen($this->botName) > 50) {
            throw new UserInputException('botName', 'tooLong');
        }

        if (empty($this->botToken)) {
            throw new UserInputException('botToken');
        }

        if (empty($this->guildID)) {
            throw new UserInputException('guildID');
        }
        if (!is_numeric($this->guildID)) {
            throw new UserInputException('guildID', 'invalid');
        }

        if (empty($this->webhookName)) {
            throw new UserInputException('webhookName');
        }
        if (strlen($this->webhookName) > 50) {
            throw new UserInputException('webhookName', 'tooLong');
        }

        if (empty($this->clientID)) {
            throw new UserInputException('clientID');
        }
        if (!is_numeric($this->clientID)) {
            throw new UserInputException('clientID', 'invalid');
        }

        if (empty($this->clientSecret)) {
            throw new UserInputException('clientSecret');
        }

        $discord = new DiscordApi($this->guildID, $this->botToken);
        $guild = $discord->getGuild();
        if ($guild['status'] != 200) {
            throw new UserInputException('guildID', 'permission_denied');
        }
        
        if (!empty($guild['body']['name'])) $this->guildName = $guild['body']['name'];
        if (!empty($guild['body']['icon'])) $this->guildIcon = $guild['body']['icon'];
    }

    public function save() {
        parent::save();

        $action = new DiscordBotAction([], 'create', [
            'data' => [
                'botName' => $this->botName,
                'botToken' => $this->botToken,
                'guildID' => $this->guildID,
                'guildName' => $this->guildName,
                'guildIcon' => $this->guildIcon,
                'webhookName' => $this->webhookName,
                'clientID' => $this->clientID,
                'clientSecret' => $this->clientSecret,
                'botTime' => TIME_NOW
            ]
        ]);
        $action->executeAction();

        $this->saved();
    }

    public function saved() {
        parent::saved();

        WCF::getTPL()->assign([
            'success' => true
        ]);
    }

    public function assignVariables() {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'action' => 'add',
            'discordBotID' => $this->discordBotID,
            'botName' => $this->botName,
            'botToken' => $this->botToken,
            'guildID' => $this->guildID,
            'webhookName' => $this->webhookName,
            'clientID' => $this->clientID,
            'clientSecret' => $this->clientSecret
        ]);
    }
}
