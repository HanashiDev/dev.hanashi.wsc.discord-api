<?php

namespace wcf\system\discord;

use Exception;
use wcf\data\discord\bot\DiscordBot;
use wcf\data\discord\webhook\DiscordWebhookAction;
use wcf\data\discord\webhook\DiscordWebhookList;
use wcf\system\cache\runtime\DiscordBotRuntimeCache;
use wcf\system\SingletonFactory;
use wcf\util\FileUtil;

final class WebhookHandler extends SingletonFactory
{
    public function saveWebhooks(int $botID, array $channelIDs, string $usageBy)
    {
        /** @var DiscordBot */
        $bot = DiscordBotRuntimeCache::getInstance()->getObject($botID);
        if ($bot === null || $channelIDs === []) {
            return;
        }
        $api = $bot->getDiscordApi();

        $avatar = null;
        $avatarFile = \sprintf('%simages/discord_webhook/%s.png', WCF_DIR, $botID);
        if (\file_exists($avatarFile)) {
            $mimeType = FileUtil::getMimeType($avatarFile);
            $avatar = 'data:' . $mimeType . ';base64,' . \base64_encode(\file_get_contents($avatarFile));
        }

        $list = new DiscordWebhookList();
        $list->getConditionBuilder()->add('botID = ?', [$bot->botID]);
        $list->getConditionBuilder()->add('channelID IN (?)', [$channelIDs]);
        $list->getConditionBuilder()->add('usageBy = ?', [$usageBy]);
        $list->readObjects();
        $existsChannelIDs = [];
        foreach ($list as $webhook) {
            $existsChannelIDs[] = $webhook->channelID;
        }

        foreach ($channelIDs as $channelID) {
            if (\in_array($channelID, $existsChannelIDs)) {
                continue;
            }

            $response = $api->createWebhook($channelID, $bot->webhookName, $avatar);
            if (!$response['error']) {
                $action = new DiscordWebhookAction([], 'create', [
                    'data' => [
                        'channelID' => $channelID,
                        'botID' => $botID,
                        'webhookID' => $response['body']['id'],
                        'webhookToken' => $response['body']['token'],
                        'webhookName' => $response['body']['name'],
                        'webhookTitle' => $usageBy,
                        'usageBy' => $usageBy,
                        'webhookTime' => TIME_NOW,
                    ],
                ]);
                $action->executeAction();
            } else {
                if (isset($response['body']['code']) && $response['body']['code'] == '30007') {
                    throw new Exception("maximum webhooks for channel {$channelID} reached");
                } else {
                    throw new Exception('unknown error on webhook creation');
                }
            }
        }
    }
}
