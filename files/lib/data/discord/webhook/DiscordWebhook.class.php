<?php

namespace wcf\data\discord\webhook;

use wcf\data\DatabaseObject;
use wcf\data\discord\bot\DiscordBot;
use wcf\system\discord\DiscordApi;

/**
 * Discord-Webhook-Objekt
 *
 * @author  Peter Lohse <hanashi@hanashi.eu>
 * @copyright   Hanashi
 * @license Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package WoltLabSuite\Core\Data\Discord\Webhook
 *
 * @property-read int $webhookID
 * @property-read int $channelID
 * @property-read int $botID
 * @property-read string $webhookToken
 * @property-read string $webhookName
 * @property-read string $webhookTitle
 * @property-read string $usageBy
 * @property-read int $webhookTime
 */
class DiscordWebhook extends DatabaseObject
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableName = 'discord_webhook';

    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'webhookID';

    /**
     * Objekt eines Discord-Bots
     */
    protected DiscordBot $discordBot;

    /**
     * Objekt der Discord-API
     */
    protected DiscordApi $discordApi;

    /**
     * gibt den zugehörigen Discord-Bot zurück
     *
     * @return DiscordBot
     */
    public function getDiscordBot(): DiscordBot
    {
        if (!isset($this->discordBot)) {
            $this->discordBot = new DiscordBot($this->botID);
        }

        return $this->discordBot;
    }

    /**
     * gibt ein Objekt der Discors-API zurück
     *
     * @return DiscordApi
     */
    public function getDiscordApi(): DiscordApi
    {
        if (!isset($this->discordApi)) {
            $this->discordApi = new DiscordApi($this->guildID, $this->botToken);
        }

        return $this->discordApi;
    }
}
