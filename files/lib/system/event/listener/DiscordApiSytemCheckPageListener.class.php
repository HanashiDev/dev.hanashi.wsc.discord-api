<?php

namespace wcf\system\event\listener;

use Override;

final class DiscordApiSytemCheckPageListener implements IParameterizedEventListener
{
    #[Override]
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        $eventObj->phpExtensions[] = 'sodium';
    }
}
