<?php

namespace wcf\data\discord\interaction\log;

use wcf\data\DatabaseObject;

/**
 * @property-read int $logID
 * @property-read string $log
 * @property-read int $time
 */
final class DiscordInteractionLog extends DatabaseObject
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableName = 'discord_interaction_log';

    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'logID';
}
