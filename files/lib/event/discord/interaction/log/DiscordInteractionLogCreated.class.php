<?php

namespace wcf\event\discord\interaction\log;

use wcf\data\discord\interaction\log\DiscordInteractionLog;
use wcf\event\IPsr14Event;

final class DiscordInteractionLogCreated implements IPsr14Event
{
    public function __construct(
        public readonly DiscordInteractionLog $bot,
    ) {
    }
}
