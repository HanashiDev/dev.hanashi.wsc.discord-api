<?php

namespace wcf\acp\form;

use wcf\data\discord\bot\DiscordBot;
use wcf\system\exception\IllegalLinkException;

class DiscordBotEditForm extends DiscordBotAddForm
{
    /**
     * @inheritDoc
     */
    public $formAction = 'edit';

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        $botID = 0;
        if (isset($_REQUEST['id'])) {
            $botID = (int)$_REQUEST['id'];
        }
        $this->formObject = new DiscordBot($botID);
        if (!$this->formObject->botID) {
            throw new IllegalLinkException();
        }
    }
}
