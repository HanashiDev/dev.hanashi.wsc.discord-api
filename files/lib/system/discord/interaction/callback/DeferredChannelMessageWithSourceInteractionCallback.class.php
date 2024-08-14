<?php

namespace wcf\system\discord\interaction\callback;

use Override;
use wcf\system\discord\DiscordApi;

final class DeferredChannelMessageWithSourceInteractionCallback extends AbstractInteractionCallback
{
    /**
     * @inheritDoc
     */
    protected int $type = DiscordApi::DISCORD_DEFERRED_CHANNEL_MESSAGE_WITH_SOURCE;

    public function __construct()
    {
    }

    #[Override]
    public function getData(): ?array
    {
        return null;
    }
}
