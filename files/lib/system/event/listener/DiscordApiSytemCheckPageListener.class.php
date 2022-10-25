<?php

namespace wcf\system\event\listener;

class DiscordApiSytemCheckPageListener implements IParameterizedEventListener
{
    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        $eventObj->phpExtensions[] = 'sodium';
    }
}
