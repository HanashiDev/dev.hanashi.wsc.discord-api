<?php

namespace wcf\system\discord\interaction\callback;

use Override;

abstract class AbstractInteractionCallback implements IDiscordInteractionCallback
{
    /**
     * the type of response
     */
    protected int $type;

    public function __construct(
        private ?InteractionCallbackData $data = null,
    ) {
    }

    #[Override]
    public function getType(): int
    {
        return $this->type;
    }

    #[Override]
    public function getData(): ?array
    {
        if (!isset($this->data) || $this->data === null) {
            return null;
        }

        return $this->data->getData();
    }

    #[Override]
    public function getInteractionResponse(): array
    {
        $response = [
            'type' => $this->getType(),
        ];

        $data = $this->getData();
        if ($data !== null) {
            $response['data'] = $data;
        }

        return $response;
    }
}
