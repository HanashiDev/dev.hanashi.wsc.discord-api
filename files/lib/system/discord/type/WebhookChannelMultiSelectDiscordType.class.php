<?php

namespace wcf\system\discord\type;

use wcf\data\discord\webhook\DiscordWebhookAction;
use wcf\data\discord\webhook\DiscordWebhookList;
use wcf\system\exception\UserInputException;
use wcf\util\FileUtil;

class WebhookChannelMultiSelectDiscordType extends ChannelMultiSelectDiscordType
{
    public function validate($newValue)
    {
        if (!\is_array($newValue) || $newValue === []) {
            return;
        }

        $botIDs = \array_keys($newValue);
        $discordBots = [];
        foreach ($this->getDiscordBotList() as $discordBot) {
            if (\in_array($discordBot->botID, $botIDs)) {
                $discordBots[$discordBot->botID] = $discordBot;
            }
        }

        $channelIDsMerged = [];
        foreach ($newValue as $channelIDs) {
            $channelIDsMerged = \array_merge($channelIDsMerged, $channelIDs);
        }

        $discordWebhookList = new DiscordWebhookList();
        $discordWebhookList->getConditionBuilder()->add('channelID IN (?)', [$channelIDsMerged]);
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
        foreach ($newValue as $botID => $channelIDs) {
            if (!\is_array($channelIDs) || $channelIDs === []) {
                continue;
            }

            if (!isset($guildChannels[$botID])) {
                throw new UserInputException($this->optionName);
            }
            if (!isset($discordBots[$botID])) {
                throw new UserInputException($this->optionName);
            }
            $channels = $guildChannels[$botID]['body'];
            $channelIDsTmp = \array_column($channels, 'id');
            foreach ($channelIDs as $channelID) {
                if (!\in_array($channelID, $channelIDsTmp)) {
                    throw new UserInputException($this->optionName);
                }

                if (!isset($webhookChannelIDs[$botID]) || !\in_array($channelID, $webhookChannelIDs[$botID])) {
                    $discordApi = $discordBots[$botID]->getDiscordApi();
                    $avatar = null;
                    $avatarFile = \sprintf('%simages/discord_webhook/%s.png', WCF_DIR, $botID);
                    if (\file_exists($avatarFile)) {
                        $mimeType = FileUtil::getMimeType($avatarFile);
                        $avatar = 'data:' . $mimeType . ';base64,' . \base64_encode(\file_get_contents($avatarFile));
                    }
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
}
