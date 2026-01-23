<?php

namespace wcf\system\discord\type;

abstract class AbstractDiscordType
{
    public function __construct(protected string $optionName)
    {
    }
}
