<?php
namespace wcf\data\discord\bot;
use wcf\data\DatabaseObject;
use wcf\system\discord\DiscordApi;
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

	// TODO: überall DiscordAPI erstzen :D
	public function getDiscordApi() {
		return new DiscordApi($this->guildID, $this->botToken);
	}
}
