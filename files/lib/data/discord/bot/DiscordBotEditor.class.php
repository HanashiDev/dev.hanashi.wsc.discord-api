<?php
namespace wcf\data\discord\bot;
use wcf\data\DatabaseObjectEditor;

class DiscordBotEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = DiscordBot::class;
}
