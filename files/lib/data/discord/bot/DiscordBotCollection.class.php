<?php

namespace wcf\data\discord\bot;

use wcf\data\DatabaseObjectCollection;
use wcf\data\file\File;
use wcf\system\cache\runtime\FileRuntimeCache;
use wcf\system\discord\DiscordApi;

/**
 * @extends DatabaseObjectCollection<DiscordBot>
 */
final class DiscordBotCollection extends DatabaseObjectCollection
{
    /**
     * @var array<int, DiscordApi>
     */
    private array $discordApis;

    private bool $webhookAvatarsLoaded = false;

    public function getApi(DiscordBot $object): DiscordApi
    {
        $this->loadDiscordApis();

        return $this->discordApis[$object->botID];
    }

    public function getWebhookAvatar(DiscordBot $object): ?File
    {
        $this->loadWebhookAvatars();

        if ($object->webhookIconID !== null) {
            return FileRuntimeCache::getInstance()->getObject($object->webhookIconID);
        }

        return null;
    }

    private function loadDiscordApis(): void
    {
        if (isset($this->discordApis)) {
            return;
        }

        $this->discordApis = [];
        foreach ($this->getObjects() as $object) {
            $this->discordApis[$object->botID] = new DiscordApi($object->guildID, $object->botToken);
        }
    }

    private function loadWebhookAvatars(): void
    {
        if ($this->webhookAvatarsLoaded) {
            return;
        }
        $this->webhookAvatarsLoaded = true;

        $webhookIconIDs = [];
        foreach ($this->getObjects() as $object) {
            if ($object->webhookIconID) {
                $webhookIconIDs[] = $object->webhookIconID;
            }
        }

        if ($webhookIconIDs !== []) {
            FileRuntimeCache::getInstance()->cacheObjectIDs($webhookIconIDs);
        }
    }
}
