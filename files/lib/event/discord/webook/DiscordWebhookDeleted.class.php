<?php

namespace wcf\event\discord\webhook;

use wcf\data\discord\webhook\DiscordWebhook;
use wcf\event\IPsr14Event;

final class DiscordWebhookDeleted implements IPsr14Event
{
    public function __construct(
        public readonly DiscordWebhook $webhook,
    ) {
    }
}
