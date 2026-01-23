<?php

namespace wcf\command\discord\webhook;

use wcf\data\discord\webhook\DiscordWebhook;
use wcf\data\discord\webhook\DiscordWebhookAction;
use wcf\event\discord\webhook\DiscordWebhookDeleted;
use wcf\system\event\EventHandler;

final class DeleteDiscordWebhook
{
    public function __construct(
        private readonly DiscordWebhook $webhook
    ) {
    }

    public function __invoke(): void
    {
        $discordApi = $this->webhook->getDiscordApi();
        $discordApi->deleteWebhookWithToken($this->webhook->webhookID, $this->webhook->webhookToken);

        $action = new DiscordWebhookAction([$this->webhook], 'delete');
        $action->executeAction();

        EventHandler::getInstance()->fire(new DiscordWebhookDeleted($this->webhook));
    }
}
