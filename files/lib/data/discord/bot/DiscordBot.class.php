<?php

namespace wcf\data\discord\bot;

use wcf\data\DatabaseObject;
use wcf\system\discord\DiscordApi;
use wcf\system\WCF;

/**
 * Discord-Bot-Objekt
 *
 * @author  Peter Lohse <hanashi@hanashi.eu>
 * @copyright   Hanashi
 * @license Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package WoltLabSuite\Core\Data\Discord\Bot
 */
class DiscordBot extends DatabaseObject
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableName = 'discord_bot';

    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'botID';

    protected $discordApi;

    public function getDiscordApi()
    {
        if ($this->discordApi === null) {
            $this->discordApi = new DiscordApi($this->guildID, $this->botToken);
        }
        return $this->discordApi;
    }
}
