<?php
namespace wcf\data\discord\server;
use wcf\data\DatabaseObjectEditor;

/**
 * Discord-Server-Objekt-Editor
 *
 * @author	Peter Lohse <hanashi@hanashi.eu>
 * @copyright	Hanashi
 * @license	Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package	WoltLabSuite\Core\Data\Discord\Server
 */
class DiscordServerEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = DiscordServer::class;
}
