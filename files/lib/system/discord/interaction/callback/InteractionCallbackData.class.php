<?php

namespace wcf\system\discord\interaction\callback;

use wcf\system\discord\DiscordApi;
use wcf\util\JSON;

final class InteractionCallbackData
{
    private array $additionalData = [];

    /**
     * @param null|bool $tts                is the response TTS
     * @param null|string $content          message content
     * @param null|array $embeds            supports up to 10 embeds
     * @param null|array $allowedMentions   allowed mentions object
     * @param null|int $flags               message flags combined as a bitfield (only `SUPPRESS_EMBEDS`, `EPHEMERAL`,
     *                                      and `SUPPRESS_NOTIFICATIONS` can be set)
     * @param null|array $components        message components
     * @param null|array $attachments       attachment objects with filename and description
     * @param null|array $poll              A poll!
     */
    public function __construct(
        private ?bool $tts = null,
        private ?string $content = null,
        private ?array $embeds = null,
        private ?array $allowedMentions = null,
        private ?int $flags = null,
        private ?array $components = null,
        private ?array $attachments = null,
        private ?array $poll = null,
    ) {
    }

    /**
     * is the response TTS
     */
    public function tts(?bool $tts = null): static
    {
        $this->tts = $tts;

        return $this;
    }

    /**
     * message content
     */
    public function content(?string $content = null): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * supports up to 10 embeds
     */
    public function embeds(?array $embeds = null): static
    {
        $this->embeds = $embeds;

        return $this;
    }

    /**
     * allowed mentions object
     */
    public function allowedMentions(?array $allowedMentions): static
    {
        $this->allowedMentions = $allowedMentions;

        return $this;
    }

    /**
     * message flags combined as a bitfield (only `SUPPRESS_EMBEDS`, `EPHEMERAL`, and `SUPPRESS_NOTIFICATIONS` can be
     * set)
     */
    public function flags(?int $flags): static
    {
        $this->flags = $flags;

        return $this;
    }

    /**
     * message components
     */
    public function components(?array $components): static
    {
        $this->components = $components;

        return $this;
    }

    /**
     * attachment objects with filename and description
     */
    public function attachments(?array $attachments): static
    {
        $this->attachments = $attachments;

        return $this;
    }

    /**
     * A poll!
     */
    public function poll(?array $poll): static
    {
        $this->poll = $poll;

        return $this;
    }

    public function ephemeral(): static
    {
        if ($this->flags === null) {
            $this->flags = DiscordApi::DISCORD_EPHEMERAL;
        } else {
            $this->flags = $this->flags | DiscordApi::DISCORD_EPHEMERAL;
        }

        return $this;
    }

    public function suppressEmbeds(): static
    {
        if ($this->flags === null) {
            $this->flags = DiscordApi::DISCORD_SUPPRESS_EMBED;
        } else {
            $this->flags = $this->flags | DiscordApi::DISCORD_SUPPRESS_EMBED;
        }

        return $this;
    }

    public function suppressNotifications(): static
    {
        if ($this->flags === null) {
            $this->flags = DiscordApi::DISCORD_SUPPRESS_NOTIFICATIONS;
        } else {
            $this->flags = $this->flags | DiscordApi::DISCORD_SUPPRESS_NOTIFICATIONS;
        }

        return $this;
    }

    public function getData(): ?array
    {
        $data = [];

        if ($this->tts !== null) {
            $data['tts'] = $this->tts;
        }
        if ($this->content !== null) {
            $data['content'] = $this->content;
        }
        if ($this->embeds !== null) {
            $data['embeds'] = $this->embeds;
        }
        if ($this->allowedMentions !== null) {
            $data['allowed_mentions'] = $this->allowedMentions;
        }
        if ($this->flags !== null) {
            $data['flags'] = $this->flags;
        }
        if ($this->components !== null) {
            $data['components'] = $this->components;
        }
        if ($this->attachments !== null) {
            $data['attachments'] = $this->attachments;
        }
        if ($this->poll !== null) {
            $data['poll'] = $this->poll;
        }

        if ($this->additionalData !== []) {
            $data = \array_merge($data, $this->additionalData);
        }

        if ($data === []) {
            return null;
        }

        return $data;
    }

    public function __toString(): string
    {
        return JSON::encode($this->getData());
    }
}
