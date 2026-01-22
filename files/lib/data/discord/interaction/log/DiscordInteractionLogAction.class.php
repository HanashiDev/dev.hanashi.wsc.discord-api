<?php

namespace wcf\data\discord\interaction\log;

use wcf\data\AbstractDatabaseObjectAction;

/**
 * @extends AbstractDatabaseObjectAction<DiscordInteractionLog, DiscordInteractionLogEditor>
 */
final class DiscordInteractionLogAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    public $className = DiscordInteractionLogEditor::class;
}
