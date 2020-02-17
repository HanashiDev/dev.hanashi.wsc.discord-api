<?php
namespace wcf\data\discord\server;
use wcf\data\DatabaseObject;
use wcf\system\discord\DiscordApi;
use wcf\system\WCF;

/**
 * Discord-Server-Objekt
 *
 * @author	Peter Lohse <hanashi@hanashi.eu>
 * @copyright	Hanashi
 * @license	Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package	WoltLabSuite\Core\Data\Discord\Server
 */
class DiscordServer extends DatabaseObject {
    /**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'discord_server';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'serverID';
}
