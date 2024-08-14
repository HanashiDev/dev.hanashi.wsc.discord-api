<?php

namespace wcf\system\discord\event;

use Override;
use wcf\system\discord\interaction\callback\IDiscordInteractionCallback;

abstract class AbstractCommandReceived implements ICommandReceived
{
    private array $data;

    protected IDiscordInteractionCallback $response;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    #[Override]
    public function getData(): array
    {
        return $this->data;
    }

    #[Override]
    public function setCallback(IDiscordInteractionCallback $response): void
    {
        $this->response = $response;
    }

    #[Override]
    public function getCallback(): ?IDiscordInteractionCallback
    {
        return $this->response ?? null;
    }
}
