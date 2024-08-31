<?php

namespace wcf\event\discord;

use wcf\event\IPsr14Event;

final class DiscordIntentsCollecting implements IPsr14Event
{
    private bool $presenceIntent = false;

    private bool $serverMembersIntent = false;

    private bool $messageContentIntent = false;

    public function presenceIntent(): void
    {
        $this->presenceIntent = true;
    }

    public function serverMembersIntent(): void
    {
        $this->serverMembersIntent = true;
    }

    public function messageContentIntent(): void
    {
        $this->messageContentIntent = true;
    }

    public function needPresenceIntent(): bool
    {
        return $this->presenceIntent;
    }

    public function needServerMembersIntent(): bool
    {
        return $this->serverMembersIntent;
    }

    public function needMessageContentIntent(): bool
    {
        return $this->messageContentIntent;
    }

    public function neededIntents(): array
    {
        $data = [];

        if ($this->presenceIntent) {
            $data[] = 'Presence Intent';
        }
        if ($this->serverMembersIntent) {
            $data[] = 'Server Members Intent';
        }
        if ($this->messageContentIntent) {
            $data[] = 'Message Content Intent';
        }

        return $data;
    }
}
