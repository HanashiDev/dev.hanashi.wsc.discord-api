<?php

namespace wcf\data\discord\webhook;

use wcf\data\CollectionDatabaseObject;
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
 *
 * @extends CollectionDatabaseObject<DiscordWebhookCollection>
 */
final class DiscordWebhook extends CollectionDatabaseObject
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
     * gibt den zugehörigen Discord-Bot zurück
     */
    public function getDiscordBot(): DiscordBot
    {
        return $this->getCollection()->getDiscordBot($this);
    }

    /**
     * gibt ein Objekt der Discors-API zurück
     */
    public function getDiscordApi(): DiscordApi
    {
        return $this->getCollection()->getDiscordApi($this);
    }
}
