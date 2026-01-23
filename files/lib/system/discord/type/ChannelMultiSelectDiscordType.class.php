<?php

namespace wcf\system\discord\type;

use Throwable;
use wcf\data\discord\bot\DiscordBotList;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

class ChannelMultiSelectDiscordType extends AbstractDiscordType
{
    /**
     * Liste von Discord-Bots
     */
    protected DiscordBotList $discordBotList;

    /**
     * Liste von Server-Channeln
     *
     * @var array<int, mixed>
     */
    protected array $guildChannels;

    /**
     * @param array<mixed> $channelTypes
     */
    public function getFormElement(mixed $value, array $channelTypes = []): string
    {
        $channels = [];
        $guildChannels = $this->getGuildChannels();
        foreach ($this->getDiscordBotList() as $discordBot) {
            $channelsTmp = [];
            if (isset($guildChannels[$discordBot->botID]['body'])) {
                $channelsTmp = $guildChannels[$discordBot->botID]['body'];
            }
            \array_multisort(\array_column($channelsTmp, 'position'), \SORT_ASC, $channelsTmp);

            $channelsGroupedTmp = [];
            foreach ($channelsTmp as $channel) {
                if (!isset($channel['parent_id'])) {
                    $childs = [];
                    if (isset($channelsGroupedTmp[$channel['id']]['childs'])) {
                        $childs = $channelsGroupedTmp[$channel['id']]['childs'];
                    }
                    $channel['childs'] = $childs;
                    $channelsGroupedTmp[$channel['id']] = $channel;
                } else {
                    $channelsGroupedTmp[$channel['parent_id']]['childs'][] = $channel;
                }
            }

            $channels[] = [
                'botID' => $discordBot->botID,
                'botName' => $discordBot->botName,
                'channels' => $channelsGroupedTmp,
            ];
        }

        $realValue = [];
        try {
            $realValue = \unserialize($value);
        } catch (Throwable) {
        }

        return WCF::getTPL()->render('wcf', 'discordChannelMultiSelect', [
            'bots' => $channels,
            'optionName' => $this->optionName,
            'value' => $realValue,
            'channelTypes' => $channelTypes,
        ]);
    }

    public function validate(mixed $newValue): void
    {
        $guildChannels = $this->getGuildChannels();
        foreach ($newValue as $botID => $channelIDs) {
            if (!\is_array($channelIDs) || $channelIDs === []) {
                continue;
            }

            if (!isset($guildChannels[$botID])) {
                throw new UserInputException($this->optionName);
            }
            $channels = [];
            if (isset($guildChannels[$botID]['body'])) {
                $channels = $guildChannels[$botID]['body'];
            }
            $channelIDsTmp = \array_column($channels, 'id');
            foreach ($channelIDs as $channelID) {
                if (!\in_array($channelID, $channelIDsTmp)) {
                    throw new UserInputException($this->optionName);
                }
            }
        }
    }

    public function getData(mixed $newValue): string
    {
        if (!\is_array($newValue)) {
            $newValue = [];
        }

        return \serialize($newValue);
    }

    /**
     * gibt Liste von Discord-Bots zurück
     */
    protected function getDiscordBotList(): DiscordBotList
    {
        if (!isset($this->discordBotList)) {
            $this->discordBotList = new DiscordBotList();
            $this->discordBotList->sqlOrderBy = 'botName ASC';
            $this->discordBotList->readObjects();
        }

        return $this->discordBotList;
    }

    /**
     * Gibt Liste von Discord-Channeln zurück
     *
     * @return array<int, mixed>
     */
    protected function getGuildChannels(): array
    {
        if (!isset($this->guildChannels)) {
            foreach ($this->getDiscordBotList() as $discordBot) {
                $guildChannelsTmp = $discordBot->getCachedDiscordChannel();
                if ($guildChannelsTmp['status'] != 200) {
                    continue;
                }
                $this->guildChannels[$discordBot->botID] = $guildChannelsTmp;
            }
        }

        return $this->guildChannels;
    }
}
