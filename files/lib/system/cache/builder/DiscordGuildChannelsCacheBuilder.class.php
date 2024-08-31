<?php

namespace wcf\system\cache\builder;

use Override;
use wcf\data\discord\bot\DiscordBotList;

final class DiscordGuildChannelsCacheBuilder extends AbstractCacheBuilder
{
    protected $maxLifetime = 86400;

    #[Override]
    public function rebuild(array $parameters)
    {
        $data = [];

        $botList = new DiscordBotList();
        $botList->readObjects();

        foreach ($botList as $bot) {
            $data[$bot->botID] = [];

            $api = $bot->getDiscordApi();
            $response = $api->getGuildChannels();
            if (!isset($response['body']) || !\is_array($response['body'])) {
                continue;
            }

            foreach ($response['body'] as $channel) {
                if (!isset($channel['id']) || !isset($channel['name'])) {
                    continue;
                }

                $data[$bot->botID][$channel['id']] = $channel;
            }
        }

        return $data;
    }
}
