<?php

namespace wcf\data\discord\bot;

use wcf\data\DatabaseObjectEditor;

/**
 * Discord-Bot-Objekt-Editor
 *
 * @author  Peter Lohse <hanashi@hanashi.eu>
 * @copyright   Hanashi
 * @license Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package WoltLabSuite\Core\Data\Discord\Bot
 *
 * @method static DiscordBot     create(array $parameters = [])
 * @method      DiscordBot     getDecoratedObject()
 * @mixin       DiscordBot
 */
final class DiscordBotEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = DiscordBot::class;
}
