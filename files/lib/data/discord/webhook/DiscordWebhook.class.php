<?php
namespace wcf\data\discord\webhook;
use wcf\data\discord\bot\DiscordBot;
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
	protected static $databaseTableIndexName = 'webhookID';

	protected $discordBot;

	public function getDiscordBot() {
		if ($this->discordBot === null) {
			$this->discordBot = new DiscordBot($this->botID);
		}
		return $this->discordBot;
	}
}
