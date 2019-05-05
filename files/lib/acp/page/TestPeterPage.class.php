<?php
namespace wcf\acp\page;
use wcf\page\AbstractPage;
use wcf\system\discord\type\ChannelMultiSelectDiscordType;
use wcf\system\WCF;

class TestPeterPage extends AbstractPage {
    public function assignVariables() {
        parent::assignVariables();

        $test = new ChannelMultiSelectDiscordType('meineoption');

        WCF::getTPL()->assign([
            'optionTemplate' => $test->getFormElement()['template']
        ]);
    }
}
