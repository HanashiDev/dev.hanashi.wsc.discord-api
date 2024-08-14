<?php

namespace wcf\system\discord\event;

use Override;
use wcf\system\discord\interaction\callback\ApplicationCommandAutocompleteResultInteractionCallback;
use wcf\system\discord\interaction\callback\IDiscordInteractionCallback;
use wcf\system\exception\InvalidObjectArgument;

final class ApplicationCommandAutocompleteReceived extends AbstractCommandReceived
{
    #[Override]
    public function setCallback(IDiscordInteractionCallback $response): void
    {
        if (!($response instanceof ApplicationCommandAutocompleteResultInteractionCallback)) {
            throw new InvalidObjectArgument(
                $response,
                ApplicationCommandAutocompleteResultInteractionCallback::class,
                'response'
            );
        }

        parent::setCallback($response);
    }
}
