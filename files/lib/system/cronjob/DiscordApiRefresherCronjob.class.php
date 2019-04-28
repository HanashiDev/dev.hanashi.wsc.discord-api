<?php
namespace wcf\system\cronjob;
use wcf\data\discord\bot\DiscordBotAction;
use wcf\data\discord\bot\DiscordBotList;
use wcf\data\discord\webhook\DiscordWebhookAction;
use wcf\data\discord\webhook\DiscordWebhookList;
use wcf\data\cronjob\Cronjob;
use wcf\system\discord\DiscordApi;
use wcf\system\WCF;

class DiscordApiRefresherCronjob extends AbstractCronjob {
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
        parent::execute($cronjob);
        
        $this->refreshGuilds();
        $this->refreshWebhooks();
    }

    protected function refreshWebhooks() {
        $discordWebhookList = new DiscordWebhookList();
        $discordWebhookList->readObjects();

        $discordApi = new DiscordApi(null, null);

        foreach ($discordWebhookList as $discordWebhook) {
            $webhook = $discordApi->getWebhookWithToken($discordWebhook->webhookID, $discordWebhook->webhookToken);
            if ($webhook['status'] != 200) continue;
            
            $webhookName = $discordWebhook->webhookName;
            if (!empty($webhook['body']['name'])) {
                $webhookName = $webhook['body']['name'];
            }
            $action = new DiscordWebhookAction([$discordWebhook], 'update', [
                'webhookName' => $webhookName
            ]);
            $action->executeAction();
        }
    }
    
    protected function refreshGuilds() {
        $discordBotList = new DiscordBotList();
        $discordBotList->readObjects();

        foreach ($discordBotList as $discordBot) {
            $discordApi = $discordBot->getDiscordApi();
            $guild = $discordApi->getGuild();
            if ($guild['status'] != 200) continue;

            $guildName = $discordBot->guildName;
            $guildIcon = $discordBot->guildIcon;
            if (!empty($guild['body']['name'])) {
                $guildName = $guild['body']['name'];
            }
            if (!empty($guild['body']['icon'])) {
                $guildIcon = $guild['body']['icon'];
            }
            
            $action = new DiscordBotAction([$discordBot], 'update', [
                'guildName' => $guildName,
                'guildIcon' => $guildIcon
            ]);
            $action->executeAction();
        }
    }
}
