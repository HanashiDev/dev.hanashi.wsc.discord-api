<?php

use wcf\system\database\table\column\BigintDatabaseTableColumn;
use wcf\system\database\table\column\NotNullInt10DatabaseTableColumn;
use wcf\system\database\table\column\TextDatabaseTableColumn;
use wcf\system\database\table\column\TinyintDatabaseTableColumn;
use wcf\system\database\table\column\VarcharDatabaseTableColumn;
use wcf\system\database\table\DatabaseTable;
use wcf\system\database\table\DatabaseTableChangeProcessor;
use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\WCF;

$tables = [
    // wcf1_category
    DatabaseTable::create('wcf1_category')
        ->columns([
            TextDatabaseTableColumn::create('discordChannelIDs'),
            VarcharDatabaseTableColumn::create('discordPostPrefix')
                ->length(30),
            TinyintDatabaseTableColumn::create('discordPostTitleInContext')
                ->length(1)
                ->notNull()
                ->defaultValue(0),
            TinyintDatabaseTableColumn::create('discordPostType')
                ->length(1)
                ->notNull()
                ->defaultValue(0),
        ]),

    // wcf1_discord_bot
    DatabaseTable::create('wcf1_discord_bot')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('botID')
                ->autoIncrement(),
            VarcharDatabaseTableColumn::create('botName')
                ->length(50)
                ->notNull(),
            VarcharDatabaseTableColumn::create('botToken')
                ->length(100)
                ->notNull(),
            BigintDatabaseTableColumn::create('guildID')
                ->length(20)
                ->notNull(),
            VarcharDatabaseTableColumn::create('guildName')
                ->length(100),
            VarcharDatabaseTableColumn::create('guildIcon')
                ->length(50),
            VarcharDatabaseTableColumn::create('webhookName')
                ->length(50)
                ->notNull(),
            BigintDatabaseTableColumn::create('clientID')
                ->length(20),
            VarcharDatabaseTableColumn::create('clientSecret')
                ->length(100),
            NotNullInt10DatabaseTableColumn::create('botTime'),
        ]),

    // wcf1_discord_webhook
    DatabaseTable::create('wcf1_discord_webhook')
        ->columns([
            BigintDatabaseTableColumn::create('webhookID')
                ->length(20)
                ->notNull(),
            BigintDatabaseTableColumn::create('channelID')
                ->length(20)
                ->notNull(),
            NotNullInt10DatabaseTableColumn::create('botID'),
            VarcharDatabaseTableColumn::create('webhookToken')
                ->length(100)
                ->notNull(),
            VarcharDatabaseTableColumn::create('webhookName')
                ->length(50)
                ->notNull(),
            VarcharDatabaseTableColumn::create('webhookTitle')
                ->length(100)
                ->notNull(),
            VarcharDatabaseTableColumn::create('usageBy')
                ->length(100)
                ->notNull(),
            NotNullInt10DatabaseTableColumn::create('webhookTime'),
        ])
        ->indices([
            DatabaseTableIndex::create()
                ->type(DatabaseTableIndex::PRIMARY_TYPE)
                ->columns(['webhookID']),
        ]),
];

(new DatabaseTableChangeProcessor(
    $this->installation->getPackage(),
    $tables,
    WCF::getDB()->getEditor())
)->process();
