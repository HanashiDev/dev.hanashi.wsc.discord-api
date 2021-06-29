<?php

namespace wcf\system\discord\type;

use wcf\system\WCF;

abstract class AbstractDiscordType
{
    protected $optionName;

    public function __construct($optionName)
    {
        $this->optionName = $optionName;
    }
}
