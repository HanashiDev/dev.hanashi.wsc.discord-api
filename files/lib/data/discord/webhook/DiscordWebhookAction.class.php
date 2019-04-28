<?php
namespace wcf\data\discord\webhook;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\discord\DiscordApi;

class DiscordWebhookAction extends AbstractDatabaseObjectAction {
    protected $permissionsDelete = ['admin.discord.canManageWebhooks'];

    public function delete() {
        foreach ($this->objects as $object) {
            $discordWebhook = $object->getDecoratedObject();
            $discordApi = DiscordApi::getApiByID($discordWebhook->botID);
            $discordApi->deleteWebhookWithToken($discordWebhook->webhookID, $discordWebhook->webhookToken);
        }
        return parent::delete();
    }
}
