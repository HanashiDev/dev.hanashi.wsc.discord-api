<?php

namespace wcf\data\discord\bot;

use wcf\data\DatabaseObject;
use wcf\data\file\File;
use wcf\system\cache\builder\DiscordGuildChannelCacheBuilder;
use wcf\system\discord\DiscordApi;

/**
 * Discord-Bot-Objekt
 *
 * @author  Peter Lohse <hanashi@hanashi.eu>
 * @copyright   Hanashi
 * @license Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package WoltLabSuite\Core\Data\Discord\Bot
 *
 * @property-read int $botID
 * @property-read string $botName
 * @property-read string $botToken
 * @property-read int $guildID
 * @property-read string|null $guildName
 * @property-read string|null $guildIcon
 * @property-read string $webhookName
 * @property-read int|null $clientID
 * @property-read string|null $clientSecret
 * @property-read string|null $publicKey
 * @property-read int $botTime
 * @property-read int|null $webhookIconID
 */
final class DiscordBot extends DatabaseObject
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableName = 'discord_bot';

    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'botID';

    protected DiscordApi $discordApi;

    public function getDiscordApi(): DiscordApi
    {
        if (!isset($this->discordApi)) {
            $this->discordApi = new DiscordApi($this->guildID, $this->botToken);
        }

        return $this->discordApi;
    }

    public function getWebhookIconUploadFileLocations(): array
    {
        $files = [];

        if ($this->webhookIconID !== null) {
            $file = new File($this->webhookIconID);
            $files[] = $file->getPath();
        }

        return $files;
    }

    public function getCachedDiscordChannel()
    {
        return DiscordGuildChannelCacheBuilder::getInstance()->getData([
            'guildID' => $this->guildID,
            'botToken' => $this->botToken,
        ]);
    }
}
