<?php
namespace wcf\system\option;
use wcf\data\discord\webhook\DiscordWebhookAction;
use wcf\data\discord\webhook\DiscordWebhookList;
use wcf\data\option\Option;
use wcf\system\discord\type\WebhookChannelMultiSelectDiscordType;
use wcf\system\discord\DiscordApi;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Option-Type für die Auswahl mehrerer Discord-Channel mit Webhook-Bezug
 *
 * @author	Peter Lohse <hanashi@hanashi.eu>
 * @copyright	Hanashi
 * @license	Freie Lizenz (https://hanashi.dev/freie-lizenz/)
 * @package	WoltLabSuite\Core\System\Option
 */
class DiscordWebhookChannelMultiSelectOptionType extends DiscordChannelMultiSelectOptionType {
    /**
     * @inheritDoc
     */
    public function validate(Option $option, $newValue) {
        $channelMultiSelectType = new WebhookChannelMultiSelectDiscordType($option->optionName);
        $channelMultiSelectType->validate($newValue);
        // if (empty($newValue)) return;

        // $botIDs = array_keys($newValue);
        // $discordBots = [];
        // foreach ($this->getDiscordBotList() as $discordBot) {
        //     if (in_array($discordBot->botID, $botIDs)) {
        //         $discordBots[$discordBot->botID] = $discordBot;
        //     }
        // }

        // $channelIDsMerged = [];
        // foreach ($newValue as $channelIDs) {
        //     $channelIDsMerged = array_merge($channelIDsMerged, $channelIDs);
        // }

        // $discordWebhookList = new DiscordWebhookList();
        // $discordWebhookList->getConditionBuilder()->add('channelID IN (?) AND usageBy = ?', [$channelIDsMerged, $option->optionName]);
        // $discordWebhookList->readObjectIDs();
        // $discordWebhooks = $discordWebhookList->objectIDs;

        // $guildChannels = $this->getGuildChannels();
        // foreach ($newValue as $botID => $channelIDs) {
        //     if (empty($channelIDs)) continue;

        //     if (!isset($guildChannels[$botID])) {
        //         throw new UserInputException($option->optionName);
        //     }
        //     if (!isset($discordBots[$botID])) {
        //         throw new UserInputException($option->optionName);
        //     }
        //     $channels = $guildChannels[$botID]['body'];
        //     $channelIDsTmp = array_column($channels, 'id');
        //     foreach ($channelIDs as $channelID) {
        //         if (!in_array($channelID, $channelIDsTmp)) {
        //             throw new UserInputException($option->optionName);
        //         }

        //         if (!in_array($channelID, $discordWebhooks)) {
        //             $discordApi = $discordBots[$botID]->getDiscordApi();
        //             $avatar = null;
        //             $avatarFile = WCF_DIR . 'images/discord_webhook/'.$botID.'.pic';
        //             if (file_exists($avatarFile)) {
        //                 $mimeType = FileUtil::getMimeType($avatarFile);
        //                 $avatar = 'data:'.$mimeType.';base64,'.base64_encode(file_get_contents($avatarFile));
        //             }
        //             $response = $discordApi->createWebhook($channelID, $discordBots[$botID]->webhookName, $avatar);
        //             if (!$response['error']) {
        //                 $action = new DiscordWebhookAction([], 'create', [
        //                     'data' => [
        //                         'channelID' => $channelID,
        //                         'botID' => $botID,
        //                         'webhookID' => $response['body']['id'],
        //                         'webhookToken' => $response['body']['token'],
        //                         'webhookName' => $response['body']['name'],
        //                         'webhookTitle' => WCF::getLanguage()->get('wcf.acp.option.'.$option->optionName),
        //                         'usageBy' => $option->optionName,
        //                         'webhookTime' => TIME_NOW
        //                     ]
        //                 ]);
        //                 $action->executeAction();
        //             } else {
        //                 if (isset($response['body']['code']) && $response['body']['code'] == '30007') {
        //                     throw new UserInputException($option->optionName, 'webhooksMaximumReached');
        //                 } else {
        //                     throw new UserInputException($option->optionName, 'webhooksUnknown');
        //                 }
        //             }
        //         }
        //     }
        // }
    }
}
