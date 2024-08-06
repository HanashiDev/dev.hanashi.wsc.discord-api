<?php

namespace wcf\system\discord\event;

use wcf\event\IPsr14Event;

abstract class AbstractCommandReceived implements IPsr14Event
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
