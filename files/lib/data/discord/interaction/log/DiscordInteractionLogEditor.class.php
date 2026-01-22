<?php

namespace wcf\data\discord\interaction\log;

use wcf\data\DatabaseObjectEditor;

/**
 * @mixin       DiscordInteractionLog
 * @extends DatabaseObjectEditor<DiscordInteractionLog>
 */
final class DiscordInteractionLogEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = DiscordInteractionLog::class;
}
