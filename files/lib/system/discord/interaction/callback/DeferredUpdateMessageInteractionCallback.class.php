<?php

namespace wcf\system\discord\interaction\callback;

use Override;
use wcf\system\discord\DiscordApi;

final class DeferredUpdateMessageInteractionCallback extends AbstractInteractionCallback
{
    /**
     * @inheritDoc
     */
    protected int $type = DiscordApi::DISCORD_DEFERRED_UPDATE_MESSAGE;

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
