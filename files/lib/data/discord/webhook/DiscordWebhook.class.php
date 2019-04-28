<?php
namespace wcf\data\discord\webhook;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

class DiscordWebhook extends DatabaseObject {
    /**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'discord_webhook';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'channelID';
}
