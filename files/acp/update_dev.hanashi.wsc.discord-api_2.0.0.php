<?php

use wcf\system\database\table\column\VarcharDatabaseTableColumn;
use wcf\system\database\table\DatabaseTable;
use wcf\system\database\table\DatabaseTableChangeProcessor;
use wcf\system\WCF;

// Neue Spalte hinzufügen
$tables = [
    // wcf1_discord_bot
    DatabaseTable::create('wcf1_discord_bot')
        ->columns([
            VarcharDatabaseTableColumn::create('publicKey')
                ->length(100),
        ]),
];

(new DatabaseTableChangeProcessor(
    $this->installation->getPackage(),
    $tables,
    WCF::getDB()->getEditor()
))->process();
