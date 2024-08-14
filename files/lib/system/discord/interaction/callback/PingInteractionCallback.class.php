<?php

namespace wcf\system\discord\interaction\callback;

use Override;
use wcf\system\discord\DiscordApi;

final class PingInteractionCallback extends AbstractInteractionCallback
{
    /**
     * @inheritDoc
     */
    protected int $type = DiscordApi::DISCORD_PING;

    #[Override]
    public function __construct()
    {
    }

    #[Override]
    public function getData(): ?array
    {
        return null;
    }
}
