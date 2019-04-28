<?php
namespace wcf\data\discord\webhook;
use wcf\data\DatabaseObjectEditor;

/**
 * Discord-Webhook-Objekt-Editor
 *
 * @author	Peter Lohse <hanashi@hanashi.eu>
 * @copyright	Hanashi
 * @license	Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package	WoltLabSuite\Core\Data\Discord\Webhook
 */
class DiscordWebhookEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = DiscordWebhook::class;
}
