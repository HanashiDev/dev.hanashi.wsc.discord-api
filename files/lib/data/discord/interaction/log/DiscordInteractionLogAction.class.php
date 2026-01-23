<?php

namespace wcf\data\discord\interaction\log;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\event\discord\interaction\log\DiscordInteractionLogCreated;
use wcf\system\event\EventHandler;

/**
 * @extends AbstractDatabaseObjectAction<DiscordInteractionLog, DiscordInteractionLogEditor>
 */
final class DiscordInteractionLogAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    public $className = DiscordInteractionLogEditor::class;

    #[\Override]
    public function create()
    {
        $log = parent::create();

        EventHandler::getInstance()->fire(new DiscordInteractionLogCreated($log));

        return $log;
    }
}
