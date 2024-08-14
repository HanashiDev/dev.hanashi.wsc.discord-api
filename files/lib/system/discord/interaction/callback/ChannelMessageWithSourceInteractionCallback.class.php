<?php

namespace wcf\system\discord\interaction\callback;

use wcf\system\discord\DiscordApi;

final class ChannelMessageWithSourceInteractionCallback extends AbstractInteractionCallback
{
    /**
     * @inheritDoc
     */
    protected int $type = DiscordApi::DISCORD_CHANNEL_MESSAGE_WITH_SOURCE;
}
