<?php

namespace wcf\data\discord\webhook;

use wcf\data\DatabaseObjectCollection;
use wcf\data\discord\bot\DiscordBot;
use wcf\system\cache\runtime\DiscordBotRuntimeCache;
use wcf\system\discord\DiscordApi;

/**
 * @extends DatabaseObjectCollection<DiscordWebhook>
 */
final class DiscordWebhookCollection extends DatabaseObjectCollection
{
    /**
     * @var array<int, DiscordApi>
     */
    private array $discordApis;

    private bool $discordBotsLoaded = false;

    public function getDiscordBot(DiscordWebhook $object): DiscordBot
    {
        $this->loadDiscordBots();

        return DiscordBotRuntimeCache::getInstance()->getObject($object->botID);
    }

    public function getDiscordApi(DiscordWebhook $object): DiscordApi
    {
        $this->loadDiscordApis();

        return $this->discordApis[$object->webhookID];
    }

    private function loadDiscordBots(): void
    {
        if ($this->discordBotsLoaded) {
            return;
        }
        $this->discordBotsLoaded = true;

        $webhookIDs = [];
        foreach ($this->getObjects() as $object) {
            $webhookIDs[] = $object->webhookID;
        }

        if ($webhookIDs !== []) {
            DiscordBotRuntimeCache::getInstance()->cacheObjectIDs($webhookIDs);
        }
    }

    private function loadDiscordApis(): void
    {
        if (isset($this->discordApis)) {
            return;
        }

        $this->discordApis = [];
        foreach ($this->getObjects() as $object) {
            $this->discordApis[$object->webhookID] = $this->getDiscordBot($object)->getDiscordApi();
        }
    }
}
