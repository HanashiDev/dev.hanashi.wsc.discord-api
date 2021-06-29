<?php

namespace wcf\acp\form;

use wcf\data\discord\bot\DiscordBot;
use wcf\data\discord\bot\DiscordBotAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Form um Discord-Bot zu bearbeiten
 *
 * @author  Peter Lohse <hanashi@hanashi.eu>
 * @copyright   Hanashi
 * @license Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package WoltLabSuite\Core\Acp\Form
 */
class DiscordBotEditForm extends DiscordBotAddForm
{
    /**
     * Objekt eines Discord-Bots
     *
     * @var DiscordBot
     */
    protected $discordBot;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['id'])) {
            $this->discordBotID = intval($_REQUEST['id']);
        }
        $this->discordBot = new DiscordBot($this->discordBotID);
        if (!$this->discordBot->botID) {
            throw new IllegalLinkException();
        }

        $this->botName = $this->discordBot->botName;
        $this->botToken = $this->discordBot->botToken;
        $this->guildID = $this->discordBot->guildID;
        $this->webhookName = $this->discordBot->webhookName;
        $this->clientID = $this->discordBot->clientID;
        $this->clientSecret = $this->discordBot->clientSecret;
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        AbstractForm::save();

        $action = new DiscordBotAction([$this->discordBot], 'update', [
            'data' => [
                'botName' => $this->botName,
                'botToken' => $this->botToken,
                'guildID' => $this->guildID,
                'guildName' => $this->guildName,
                'guildIcon' => $this->guildIcon,
                'webhookName' => $this->webhookName,
                'clientID' => $this->clientID,
                'clientSecret' => $this->clientSecret
            ]
        ]);
        $action->executeAction();

        $this->saved();
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'action' => 'edit'
        ]);
    }
}
