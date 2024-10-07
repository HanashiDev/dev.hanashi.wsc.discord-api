<?php

namespace wcf\data\discord\bot;

use wcf\data\DatabaseObject;
use wcf\data\file\File;
use wcf\system\cache\builder\DiscordGuildChannelCacheBuilder;
use wcf\system\discord\DiscordApi;
use wcf\system\WCF;

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

    protected ?File $file;

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
            $files[] = $file->getPathname();
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

    public function getWebhookAvatar(): ?File
    {
        if ($this->webhookIconID === null) {
            return null;
        }

        if (!isset($this->file)) {
            $this->file = new File($this->webhookIconID);
        }

        return $this->file;
    }

    public function getWebhookAvatarData(): ?string
    {
        $file = $this->getWebhookAvatar();
        if ($file === null) {
            return null;
        }

        return 'data:' . $file->mimeType . ';base64,' . \base64_encode(\file_get_contents($file->getPathname()));
    }

    public static function findByFileID(int $fileID): ?DiscordBot
    {
        $sql = "
            SELECT  *
            FROM    wcf1_discord_bot
            WHERE   botID = ?
        ";
        $stmnt = WCF::getDB()->prepare($sql);
        $stmnt->execute([$fileID]);

        return $stmnt->fetchObject(DiscordBot::class);
    }
}
