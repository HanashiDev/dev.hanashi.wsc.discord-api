<?php

namespace wcf\system\discord\event;

use wcf\system\event\IEvent;

abstract class AbstractCommandReceived implements IEvent
{
    /**
     * @var array
     */
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
