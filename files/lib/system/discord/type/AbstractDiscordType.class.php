<?php

namespace wcf\system\discord\type;

abstract class AbstractDiscordType
{
    protected $optionName;

    public function __construct($optionName)
    {
        $this->optionName = $optionName;
    }
}
