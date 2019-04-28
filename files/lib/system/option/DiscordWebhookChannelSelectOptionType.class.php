<?php
namespace wcf\system\option;
use wcf\data\discord\webhook\DiscordWebhookAction;
use wcf\data\discord\webhook\DiscordWebhookList;
use wcf\data\option\Option;
use wcf\system\discord\DiscordApi;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

class DiscordWebhookChannelSelectOptionType extends DiscordChannelSelectOptionType {
    /**
     * @inheritDoc
     */
    public function validate(Option $option, $newValue) {
        $botIDs = array_keys($newValue);
        $discordBots = [];
        foreach ($this->getDiscordBotList() as $discordBot) {
            if (in_array($discordBot->botID, $botIDs)) {
                $discordBots[$discordBot->botID] = $discordBot;
            }
        }

        $discordWebhookList = new DiscordWebhookList();
        $discordWebhookList->getConditionBuilder()->add('channelID IN (?) AND usageBy = ?', [array_values($newValue), $option->optionName]);
        $discordWebhookList->readObjectIDs();
        $discordWebhooks = $discordWebhookList->objectIDs;

        $guildChannels = $this->getGuildChannels();
        foreach ($newValue as $botID => $channelID) {
            if (empty($channelID)) continue;

            if (!isset($guildChannels[$botID])) {
                throw new UserInputException($option->optionName);
            }
            if (!isset($discordBots[$botID])) {
                throw new UserInputException($option->optionName);
            }
            $channels = $guildChannels[$botID]['body'];
            $channelIDs = array_column($channels, 'id');
            if (!in_array($channelID, $channelIDs)) {
                throw new UserInputException($option->optionName);
            }

            if (!in_array($channelID, $discordWebhooks)) {
                $discordApi = new DiscordApi($discordBots[$botID]->guildID, $discordBots[$botID]->botToken);
                $response = $discordApi->createWebhook($channelID, $discordBots[$botID]->webhookName);
                if (!$response['error']) {
                    $action = new DiscordWebhookAction([], 'create', [
                        'data' => [
                            'channelID' => $channelID,
                            'botID' => $botID,
                            'webhookID' => $response['body']['id'],
                            'webhookToken' => $response['body']['token'],
                            'webhookName' => $response['body']['name'],
                            'webhookTitle' => WCF::getLanguage()->get('wcf.acp.option.'.$option->optionName),
                            'usageBy' => $option->optionName
                        ]
                    ]);
                    $action->executeAction();
                } else {
                    if (isset($response['body']['code']) && $response['body']['code'] == '30007') {
                        throw new UserInputException($option->optionName, 'webhooksMaximumReached');
                    } else {
                        throw new UserInputException($option->optionName, 'webhooksUnknown');
                    }
                }
            }
        }
    }
}
