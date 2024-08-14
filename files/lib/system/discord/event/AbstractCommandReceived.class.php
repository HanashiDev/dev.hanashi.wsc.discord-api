<?php

namespace wcf\system\discord\event;

use wcf\event\IPsr14Event;

abstract class AbstractCommandReceived implements IPsr14Event
{
    private array $data;

    private array $response = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setResponse(array $response)
    {
        return $this->response = $response;
    }

    public function getResponse(): array
    {
        return $this->response;
    }
}
