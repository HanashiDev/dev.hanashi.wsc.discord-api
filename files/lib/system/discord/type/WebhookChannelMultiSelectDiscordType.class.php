<?php

namespace wcf\system\discord\type;

use wcf\data\discord\webhook\DiscordWebhookAction;
use wcf\data\discord\webhook\DiscordWebhookList;
use wcf\system\discord\DiscordApi;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\FileUtil;

class WebhookChannelMultiSelectDiscordType extends ChannelMultiSelectDiscordType
{
    public function validate($newValue)
    {
        if (empty($newValue)) {
            return;
        }

        $botIDs = array_keys($newValue);
        $discordBots = [];
        foreach ($this->getDiscordBotList() as $discordBot) {
            if (in_array($discordBot->botID, $botIDs)) {
                $discordBots[$discordBot->botID] = $discordBot;
            }
        }

        $channelIDsMerged = [];
        foreach ($newValue as $channelIDs) {
            $channelIDsMerged = array_merge($channelIDsMerged, $channelIDs);
        }

        $discordWebhookList = new DiscordWebhookList();
        $discordWebhookList->getConditionBuilder()->add('channelID IN (?) AND usageBy = ?', [$channelIDsMerged, $this->optionName]);
        $discordWebhookList->readObjects();
        $webhookChannelIDs = [];
        foreach ($discordWebhookList as $discordWebhook) {
            if (!in_array($discordWebhook->channelID, $webhookChannelIDs)) {
                $webhookChannelIDs[] = $discordWebhook->channelID;
            }
        }

        $guildChannels = $this->getGuildChannels();
        foreach ($newValue as $botID => $channelIDs) {
            if (empty($channelIDs)) {
                continue;
            }

            if (!isset($guildChannels[$botID])) {
                throw new UserInputException($this->optionName);
            }
            if (!isset($discordBots[$botID])) {
                throw new UserInputException($this->optionName);
            }
            $channels = $guildChannels[$botID]['body'];
            $channelIDsTmp = array_column($channels, 'id');
            foreach ($channelIDs as $channelID) {
                if (!in_array($channelID, $channelIDsTmp)) {
                    throw new UserInputException($this->optionName);
                }

                if (!in_array($channelID, $webhookChannelIDs)) {
                    $discordApi = $discordBots[$botID]->getDiscordApi();
                    $avatar = null;
                    $avatarFile = sprintf('%simages/discord_webhook/%s.png', WCF_DIR, $botID);
                    if (file_exists($avatarFile)) {
                        $mimeType = FileUtil::getMimeType($avatarFile);
                        $avatar = 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($avatarFile));
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
                                'webhookTime' => TIME_NOW
                            ]
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
