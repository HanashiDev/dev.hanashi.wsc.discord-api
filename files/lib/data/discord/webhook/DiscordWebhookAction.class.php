<?php
namespace wcf\data\discord\webhook;
use wcf\data\AbstractDatabaseObjectAction;

class DiscordWebhookAction extends AbstractDatabaseObjectAction {
    // TODO: webhook recht
    protected $permissionsDelete = ['admin.discord.canManageConnection'];
}
