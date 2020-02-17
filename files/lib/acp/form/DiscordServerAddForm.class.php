<?php
namespace wcf\acp\form;
use wcf\data\discord\server\DiscordServerAction;
use wcf\form\AbstractForm;
use wcf\system\discord\DiscordApi;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Form um Discord-Server hinzuzufügen
 *
 * @author	Peter Lohse <hanashi@hanashi.eu>
 * @copyright	Hanashi
 * @license	Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package	WoltLabSuite\Core\Acp\Form
 */
class DiscordServerAddForm extends AbstractForm {
    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.discord.canManageConnection'];

    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.configuration.discord.discordServerList.add';

    /**
     * ID des Discord-Servers
     * 
     * @var integer
     */
    protected $discordServerID;

    /**
     * ID des Discord-Bots
     * 
     * @var integer
     */
    protected $botID;

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

        if (isset($_POST['botID'])) $this->botID = StringUtil::trim($_POST['botID']);
        if (isset($_POST['guildID'])) $this->guildID = StringUtil::trim($_POST['guildID']);
        if (isset($_POST['webhookName'])) $this->webhookName = StringUtil::trim($_POST['webhookName']);
        if (isset($_FILES['webhookIcon'])) $this->webhookIcon = $_FILES['webhookIcon'];
    }

    /**
     * @inheritDoc
     */
    public function validate() {
        parent::validate();

        // TODO: validate botID

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

        // TODO: richtig einbauen
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

        $action = new DiscordServerAction([], 'create', [
            'data' => [
                // 'botID' => $this->botID,
                'guildID' => $this->guildID,
                'guildName' => $this->guildName,
                'guildIcon' => $this->guildIcon,
                'webhookName' => $this->webhookName,
                'serverTime' => TIME_NOW
            ]
        ]);
        // TODO: Discord-Server
        // $discordBot = $action->executeAction()['returnValues'];
        // if (!empty($this->webhookIcon['tmp_name'])) {
        //     move_uploaded_file($this->webhookIcon['tmp_name'], WCF_DIR.'images/discord_webhook/'.$discordBot->botID.'.pic');
        // }

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
            'discordServerID' => $this->discordServerID,
            'botID' => $this->botID,
            'guildID' => $this->guildID,
            'webhookName' => $this->webhookName
        ]);
    }
}
