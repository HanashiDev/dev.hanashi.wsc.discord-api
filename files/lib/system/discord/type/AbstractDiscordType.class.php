<?php
namespace wcf\system\discord\type;
use wcf\system\WCF;

abstract class AbstractDiscordType {
    protected $optionName;
    protected $value;

    public function __construct($optionName, $value = null) {
        $this->optionName = $optionName;
        $this->value = $value;
    }

    public function setValue($value) {
        $this->value = $value;
    }
}
