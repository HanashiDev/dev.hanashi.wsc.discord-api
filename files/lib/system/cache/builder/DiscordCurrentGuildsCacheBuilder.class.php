<?php

namespace wcf\system\cache\builder;

use wcf\system\discord\DiscordApi;

/**
 * this class is a workaround because of rate limits
 *
 * @author  Peter Lohse <hanashi@hanashi.eu>
 * @copyright   Hanashi
 * @license Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package WoltLabSuite\Core\System\Cache\Builder
 */
class DiscordCurrentGuildsCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritDoc
     */
    protected $maxLifetime = 10;

    /**
     * @inheritDoc
     */
    public function rebuild(array $parameters)
    {
        if (empty($parameters['botToken'])) {
            return [];
        }
        $discord = new DiscordApi(0, $parameters['botToken']);

        $currentUserGuilds = $discord->getCurrentUserGuilds();
        if (!isset($currentUserGuilds['body']) || \count($currentUserGuilds['body']) && empty($currentUserGuilds['body'][0])) {
            return [];
        }
        $guilds = $currentUserGuilds['body'];
        \usort($guilds, static fn ($a, $b) => \strtoupper($a['name']) <=> \strtoupper($b['name']));

        $newGuilds = [];
        foreach ($guilds as $guild) {
            $newGuilds[$guild['id']] = $guild['name'];
        }

        return $newGuilds;
    }
}
