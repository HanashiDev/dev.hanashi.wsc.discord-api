<?php

namespace wcf\event\interaction\admin;

use wcf\event\IPsr14Event;
use wcf\system\interaction\admin\DiscordBotInteractions;

final class DiscordBotInteractionCollecting implements IPsr14Event
{
    public function __construct(public readonly DiscordBotInteractions $provider)
    {
    }
}
