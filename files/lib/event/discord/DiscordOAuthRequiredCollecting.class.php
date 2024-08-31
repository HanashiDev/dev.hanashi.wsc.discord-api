<?php

namespace wcf\event\discord;

use wcf\event\IPsr14Event;

final class DiscordOAuthRequiredCollecting implements IPsr14Event
{
    private bool $needOauth2 = false;

    public function register(): void
    {
        $this->needOauth2 = true;
    }

    public function needOauth2(): bool
    {
        return $this->needOauth2;
    }
}
