<?php
namespace wcf\acp\form;
use wcf\data\discord\bot\DiscordBotAction;
use wcf\form\AbstractForm;
use wcf\system\discord\DiscordApi;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Form um Discord-Bot hinzuzufügen
 *
 * @author	Peter Lohse <hanashi@hanashi.eu>
 * @copyright	Hanashi
 * @license	Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package	WoltLabSuite\Core\Acp\Form
 */
class DiscordBotAddForm extends AbstractForm {
    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.discord.canManageConnection'];

    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.configuration.discord.discordBotList.add';

    /**
     * ID des Discord-Bots
     * 
     * @var integer
     */
    protected $discordBotID;

    /**
     * Anzeigename des Discord-Bots
     * 
     * @var string
     */
    protected $botName = 'Default';

    /**
     * Token des Bots
     * 
     * @var string
     */
    protected $botToken;

    /**
     * ID des Discord-Servers
     * 
     * @var integer
     */
    protected $guildID;

    /**
     * standardisierte Webhook-Name
     * 
     * @var string
     */
    protected $webhookName = PAGE_TITLE;

    /**
     * Client-ID der Discord-Anwendung
     * 
     * @var intger
     */
    protected $clientID;

    /**
     * Geheimer Schlüssel der Discord-Anwendung
     * 
     * @var string
     */
    protected $clientSecret;

    /**
     * Hochgeladenes Icon
     * 
     * @var array
     */
    protected $webhookIcon;

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

    /**
     * @inheritDoc
     */
    public function readFormParameters() {
        parent::readFormParameters();

        if (isset($_POST['botName'])) $this->botName = StringUtil::trim($_POST['botName']);
        if (isset($_POST['botToken'])) $this->botToken = StringUtil::trim($_POST['botToken']);
        if (isset($_POST['guildID'])) $this->guildID = StringUtil::trim($_POST['guildID']);
        if (isset($_POST['webhookName'])) $this->webhookName = StringUtil::trim($_POST['webhookName']);
        if (isset($_POST['clientID'])) $this->clientID = StringUtil::trim($_POST['clientID']);
        if (isset($_POST['clientSecret'])) $this->clientSecret = StringUtil::trim($_POST['clientSecret']);
        if (isset($_FILES['webhookIcon'])) $this->webhookIcon = $_FILES['webhookIcon'];
    }

    /**
     * @inheritDoc
     */
    public function validate() {
        parent::validate();

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

        if (!empty($this->webhookIcon['size'])) {
            if ($this->webhookIcon['size'] > 256000) {
                throw new UserInputException('webhookIcon', 'tooBig');
            }
            if (!in_array($this->webhookIcon['type'], ['image/jpeg', 'image/png', 'image/gif'])) {
                throw new UserInputException('webhookIcon', 'unknownFormat');
            }
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

    /**
     * @inheritDoc 
     */
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
        $discordBot = $action->executeAction()['returnValues'];
        if (!empty($this->webhookIcon['tmp_name'])) {
            move_uploaded_file($this->webhookIcon['tmp_name'], WCF_DIR.'images/discord_webhook/'.$discordBot->botID.'.pic');
        }

        $this->saved();
    }

    /**
     * @inheritDoc
     */
    public function saved() {
        parent::saved();

        WCF::getTPL()->assign([
            'success' => true
        ]);
    }

    /**
     * @inheritDoc
     */
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
