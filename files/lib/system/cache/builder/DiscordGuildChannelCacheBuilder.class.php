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
final class DiscordGuildChannelCacheBuilder extends AbstractCacheBuilder
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
        if (
            !isset($parameters['guildID'])
            || !isset($parameters['botToken'])
            || $parameters['guildID'] === ''
            || $parameters['botToken'] === ''
        ) {
            return [];
        }

        $discordApi = new DiscordApi($parameters['guildID'], $parameters['botToken']);

        return $discordApi->getGuildChannels();
    }
}
