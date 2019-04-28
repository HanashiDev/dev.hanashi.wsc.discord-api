<?php
namespace wcf\data\discord\webhook;
use wcf\data\DatabaseObjectEditor;

class DiscordWebhookEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = DiscordWebhook::class;
}
