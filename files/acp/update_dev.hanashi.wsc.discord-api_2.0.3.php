<?php

use wcf\system\database\table\column\VarcharDatabaseTableColumn;
use wcf\system\database\table\DatabaseTableChangeProcessor;
use wcf\system\database\table\PartialDatabaseTable;
use wcf\system\WCF;

$tables = [
    // wcf1_category
    PartialDatabaseTable::create('wcf1_category')
        ->columns([
            VarcharDatabaseTableColumn::create('discordPostPrefix')
                ->length(100),
        ]),
];

(new DatabaseTableChangeProcessor(
    $this->installation->getPackage(),
    $tables,
    WCF::getDB()->getEditor()
))->process();
