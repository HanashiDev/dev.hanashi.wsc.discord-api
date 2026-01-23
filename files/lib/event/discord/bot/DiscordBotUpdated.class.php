<?php

namespace wcf\event\discord\bot;

use wcf\data\discord\bot\DiscordBot;
use wcf\event\IPsr14Event;

final class DiscordBotUpdated implements IPsr14Event
{
    public function __construct(
        public readonly DiscordBot $bot,
    ) {
    }
}
