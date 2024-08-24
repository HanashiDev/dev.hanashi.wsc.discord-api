<?php

namespace wcf\event\discord;

use wcf\event\IPsr14Event;

final class DiscordPublicKeyRequiredCollecting implements IPsr14Event
{
    private bool $needPublicKey = false;

    public function register(): void
    {
        $this->needPublicKey = true;
    }

    public function needPublicKey(): bool
    {
        return $this->needPublicKey;
    }
}
