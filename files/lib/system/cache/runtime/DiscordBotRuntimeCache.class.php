<?php

namespace wcf\system\cache\runtime;

use wcf\data\discord\bot\DiscordBotList;

/**
 * @author  Peter Lohse <hanashi@hanashi.eu>
 * @copyright   Hanashi
 * @license Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package WoltLabSuite\Core\System\Cache\Runtime
 */
class DiscordBotRuntimeCache extends AbstractRuntimeCache
{
    /**
     * @inheritDoc
     */
    protected $listClassName = DiscordBotList::class;
}
