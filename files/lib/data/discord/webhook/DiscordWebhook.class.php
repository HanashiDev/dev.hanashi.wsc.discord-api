<?php
namespace wcf\data\discord\webhook;
use wcf\data\discord\bot\DiscordBot;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Discord-Webhook-Objekt
 *
 * @author	Peter Lohse <hanashi@hanashi.eu>
 * @copyright	Hanashi
 * @license	Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package	WoltLabSuite\Core\Data\Discord\Webhook
 */
class DiscordWebhook extends DatabaseObject {
    /**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'discord_webhook';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'webhookID';

	/**
	 * Objekt eines Discord-Bots
	 * 
	 * @var DiscordBot
	 */
	protected $discordBot;

	/**
	 * gibt den zugehörigen Discord-Bot zurück
	 * 
	 * @return DiscordBot
	 */
	public function getDiscordBot() {
		if ($this->discordBot === null) {
			$this->discordBot = new DiscordBot($this->botID);
		}
		return $this->discordBot;
	}
}
