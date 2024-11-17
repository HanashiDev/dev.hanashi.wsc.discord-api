<?php

namespace wcf\system\event\gridView;

use wcf\event\IPsr14Event;
use wcf\system\gridView\DiscordBotListGridView;

final class DiscordBotListGridViewInitialized implements IPsr14Event
{
    public function __construct(public readonly DiscordBotListGridView $gridView)
    {
    }
}
