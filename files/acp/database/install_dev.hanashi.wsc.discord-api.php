<?php

use wcf\system\database\table\column\BigintDatabaseTableColumn;
use wcf\system\database\table\column\BlobDatabaseTableColumn;
use wcf\system\database\table\column\IntDatabaseTableColumn;
use wcf\system\database\table\column\NotNullInt10DatabaseTableColumn;
use wcf\system\database\table\column\ObjectIdDatabaseTableColumn;
use wcf\system\database\table\column\TextDatabaseTableColumn;
use wcf\system\database\table\column\TinyintDatabaseTableColumn;
use wcf\system\database\table\column\VarcharDatabaseTableColumn;
use wcf\system\database\table\DatabaseTable;
use wcf\system\database\table\index\DatabaseTableForeignKey;
use wcf\system\database\table\index\DatabaseTablePrimaryIndex;
use wcf\system\database\table\PartialDatabaseTable;

return [
    // wcf1_category
    PartialDatabaseTable::create('wcf1_category')
        ->columns([
            TextDatabaseTableColumn::create('discordChannelIDs'),
            VarcharDatabaseTableColumn::create('discordPostPrefix')
                ->length(100),
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
            ObjectIdDatabaseTableColumn::create('botID'),
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
            VarcharDatabaseTableColumn::create('publicKey')
                ->length(100),
            NotNullInt10DatabaseTableColumn::create('botTime'),
            IntDatabaseTableColumn::create('webhookIconID')
                ->length(10),
        ])
        ->indices([
            DatabaseTablePrimaryIndex::create()
                ->columns(['botID']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['webhookIconID'])
                ->referencedTable('wcf1_file')
                ->referencedColumns(['fileID'])
                ->onDelete('SET NULL'),
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
            DatabaseTablePrimaryIndex::create()
                ->columns(['webhookID']),
        ]),

    // wcf1_discord_interaction_log
    DatabaseTable::create('wcf1_discord_interaction_log')
        ->columns([
            ObjectIdDatabaseTableColumn::create('logID'),
            BlobDatabaseTableColumn::create('log')
                ->notNull(),
            NotNullInt10DatabaseTableColumn::create('time'),
        ])
        ->indices([
            DatabaseTablePrimaryIndex::create()
                ->columns(['logID']),
        ]),
];
