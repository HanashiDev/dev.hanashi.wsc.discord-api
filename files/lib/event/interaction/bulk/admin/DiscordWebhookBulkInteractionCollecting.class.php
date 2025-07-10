<?php

namespace wcf\event\interaction\bulk\admin;

use wcf\event\IPsr14Event;
use wcf\system\interaction\bulk\admin\DiscordWebhookBulkInteractions;

final class DiscordWebhookBulkInteractionCollecting implements IPsr14Event
{
    public function __construct(public readonly DiscordWebhookBulkInteractions $provider)
    {
    }
}
