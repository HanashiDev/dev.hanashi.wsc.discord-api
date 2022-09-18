<?php

namespace wcf\system\option;

use wcf\data\option\Option;
use wcf\system\exception\UserInputException;
use wcf\system\Regex;
use wcf\system\WCF;

class DiscordEmbedColorOptionType extends AbstractOptionType
{
    /**
     * @inheritDoc
     */
    public function getFormElement(Option $option, $value)
    {
        $hex = \str_pad(\dechex($value), 6, '0', \STR_PAD_LEFT);
        $colorParts = \explode(' ', \chunk_split($hex, 2, ' '));
        if (\count($colorParts) < 3) {
            $value = 'rgba(0, 0, 0, 1)';
        } else {
            $value = \sprintf(
                'rgba(%s, %s, %s, 1)',
                \hexdec($colorParts[0]),
                \hexdec($colorParts[1]),
                \hexdec($colorParts[2])
            );
        }

        return WCF::getTPL()->fetch('discordEmbedColorOptionType', 'wcf', [
            'option' => $option,
            'value' => $value,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validate(Option $option, $newValue)
    {
        if (!empty($newValue)) {
            $regex = new Regex('rgba\(\d{1,3}, \d{1,3}, \d{1,3}, (1|1\.00?|0|0?\.[0-9]{1,2})\)');

            if (!$regex->match($newValue)) {
                throw new UserInputException($option->optionName);
            }
        }
    }

    public function getData(Option $option, $newValue)
    {
        \preg_match('/rgba\((\d{1,3}), (\d{1,3}), (\d{1,3}), (1|1\.00?|0|0?\.[0-9]{1,2})\)/', $newValue, $matches);
        $hex = \sprintf(
            '%s%s%s',
            \str_pad(\dechex($matches[1]), 2, '0', \STR_PAD_LEFT),
            \str_pad(\dechex($matches[2]), 2, '0', \STR_PAD_LEFT),
            \str_pad(\dechex($matches[3]), 2, '0', \STR_PAD_LEFT),
        );

        return \hexdec($hex);
    }
}
