<?php

namespace wcf\data\discord\bot;

use wcf\data\CollectionDatabaseObject;
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
 * @property-read ?string $guildName
 * @property-read ?string $guildIcon
 * @property-read string $webhookName
 * @property-read ?int $clientID
 * @property-read ?string $clientSecret
 * @property-read ?string $publicKey
 * @property-read int $botTime
 * @property-read ?int $webhookIconID
 *
 * @extends CollectionDatabaseObject<DiscordBotCollection>
 */
final class DiscordBot extends CollectionDatabaseObject
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableName = 'discord_bot';

    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'botID';

    public function getDiscordApi(): DiscordApi
    {
        return $this->getCollection()->getApi($this);
    }

    public function getWebhookIconUploadFileLocations(): array
    {
        $file = $this->getWebhookAvatar();
        if ($file === null) {
            return [];
        }

        return [$file->getPathname()];
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
        return $this->getCollection()->getWebhookAvatar($this);
    }

    public function getWebhookAvatarData(): ?string
    {
        $file = $this->getWebhookAvatar();
        if ($file === null) {
            return null;
        }

        return 'data:' . $file->mimeType . ';base64,' . \base64_encode(\file_get_contents($file->getPathname()));
    }

    public static function findByFileID(int $fileID): ?self
    {
        $sql = "
            SELECT  *
            FROM    wcf1_discord_bot
            WHERE   webhookIconID = ?
        ";
        $stmnt = WCF::getDB()->prepare($sql);
        $stmnt->execute([$fileID]);

        return $stmnt->fetchObject(self::class);
    }
}
