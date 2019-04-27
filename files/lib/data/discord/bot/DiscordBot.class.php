<?php
namespace wcf\data\discord\bot;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

class DiscordBot extends DatabaseObject {
    /**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'discord_bot';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'botID';
}
