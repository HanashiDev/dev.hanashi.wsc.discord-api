<?php

namespace wcf\event\interaction\admin;

use wcf\event\IPsr14Event;
use wcf\system\interaction\admin\DiscordWebhookInteractions;

final class DiscordWebhookInteractionCollecting implements IPsr14Event
{
    public function __construct(public readonly DiscordWebhookInteractions $provider)
    {
    }
}
