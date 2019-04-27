<?php
namespace wcf\action;
use wcf\system\discord\DiscordApi;

// TODO: Klasse wieder löschen
class DiscordTestAction extends AbstractAction {
    public function execute() {
        parent::execute();

        $discord = new DiscordApi(388240292400332802, 'Mzg4MjQwNDEwODg5Mjg5NzI4.XMP3zw._sA_XWOEqVXsygFvIOI6gdNB0ZI');
        wcfDebug($discord->getGuildAuditLog());
    }
}
