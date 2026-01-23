<?php

namespace wcf\command\discord\interaction\log;

use wcf\data\discord\interaction\log\DiscordInteractionLogAction;

final class LogDiscordInteraction
{
    public function __construct(
        private readonly string $log
    ) {
    }

    public function __invoke(): void
    {
        $action = new DiscordInteractionLogAction([], 'create', [
            'data' => [
                'log' => $this->log,
                'time' => \TIME_NOW,
            ],
        ]);
        $action->executeAction();
    }
}
