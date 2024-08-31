<?php

namespace wcf\data\discord\interaction\log;

use wcf\data\DatabaseObjectEditor;

/**
 * @method static DiscordInteractionLog     create(array $parameters = [])
 * @method      DiscordInteractionLog     getDecoratedObject()
 * @mixin       DiscordInteractionLog
 */
final class DiscordInteractionLogEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = DiscordInteractionLog::class;
}
