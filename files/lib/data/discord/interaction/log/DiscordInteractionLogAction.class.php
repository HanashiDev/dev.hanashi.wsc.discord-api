<?php

namespace wcf\data\discord\interaction\log;

use wcf\data\AbstractDatabaseObjectAction;

/**
 * @method  DiscordInteractionLogEditor[] getObjects()
 * @method  DiscordInteractionLogEditor   getSingleObject()
 */
final class DiscordInteractionLogAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    public $className = DiscordInteractionLogEditor::class;
}
