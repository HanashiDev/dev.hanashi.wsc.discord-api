<?php

namespace wcf\system\discord\type;

use Override;
use wcf\data\discord\webhook\DiscordWebhookAction;
use wcf\data\discord\webhook\DiscordWebhookList;
use wcf\system\exception\UserInputException;

class WebhookChannelSelectDiscordType extends ChannelSelectDiscordType
{
    #[Override]
    public function validate($newValue, ?int $maxChannels = null)
    {
        if (!\is_array($newValue) || $newValue === []) {
            return;
        }
        if (!\is_array($newValue)) {
            throw new UserInputException($this->optionName);
        }
        if ($maxChannels !== null && \count($newValue) > $maxChannels) {
            throw new UserInputException($this->optionName, 'discordMaxChannels');
        }

        $botIDs = \array_keys($newValue);
        $discordBots = [];
        foreach ($this->getDiscordBotList() as $discordBot) {
            if (\in_array($discordBot->botID, $botIDs)) {
                $discordBots[$discordBot->botID] = $discordBot;
            }
        }

        $discordWebhookList = new DiscordWebhookList();
        $discordWebhookList->getConditionBuilder()->add('channelID IN (?)', [\array_values($newValue)]);
        $discordWebhookList->getConditionBuilder()->add('usageBy = ?', [$this->optionName]);
        $discordWebhookList->getConditionBuilder()->add('botID IN (?)', [$botIDs]);
        $discordWebhookList->readObjects();
        $webhookChannelIDs = [];
        foreach ($discordWebhookList as $discordWebhook) {
            if (
                !isset($webhookChannelIDs[$discordWebhook->botID])
                || !\is_array($webhookChannelIDs[$discordWebhook->botID])
                || !\in_array($discordWebhook->channelID, $webhookChannelIDs[$discordWebhook->botID])
            ) {
                $webhookChannelIDs[$discordWebhook->botID][] = $discordWebhook->channelID;
            }
        }

        $guildChannels = $this->getGuildChannels();
        foreach ($newValue as $botID => $channelID) {
            if (!isset($guildChannels[$botID])) {
                throw new UserInputException($this->optionName);
            }
            if (!isset($discordBots[$botID])) {
                throw new UserInputException($this->optionName);
            }
            $channels = $guildChannels[$botID]['body'];
            $channelIDs = \array_column($channels, 'id');
            if (!\in_array($channelID, $channelIDs)) {
                throw new UserInputException($this->optionName);
            }

            if (!isset($webhookChannelIDs[$botID]) || !\in_array($channelID, $webhookChannelIDs[$botID])) {
                $discordApi = $discordBots[$botID]->getDiscordApi();
                $avatar = $discordBots[$botID]->getWebhookAvatarData();
                $response = $discordApi->createWebhook($channelID, $discordBots[$botID]->webhookName, $avatar);
                if (!$response['error']) {
                    $action = new DiscordWebhookAction([], 'create', [
                        'data' => [
                            'channelID' => $channelID,
                            'botID' => $botID,
                            'webhookID' => $response['body']['id'],
                            'webhookToken' => $response['body']['token'],
                            'webhookName' => $response['body']['name'],
                            'webhookTitle' => $this->optionName,
                            'usageBy' => $this->optionName,
                            'webhookTime' => TIME_NOW,
                        ],
                    ]);
                    $action->executeAction();
                } else {
                    if (isset($response['body']['code']) && $response['body']['code'] == '30007') {
                        throw new UserInputException($this->optionName, 'webhooksMaximumReached');
                    } else {
                        throw new UserInputException($this->optionName, 'webhooksUnknown');
                    }
                }
            }
        }
    }
}
