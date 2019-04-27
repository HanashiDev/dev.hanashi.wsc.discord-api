<?php
namespace wcf\data\discord\bot;
use wcf\data\AbstractDatabaseObjectAction;

class DiscordBotAction extends AbstractDatabaseObjectAction {
    protected $permissionsDelete = ['admin.discord.canManageConnection'];
}
