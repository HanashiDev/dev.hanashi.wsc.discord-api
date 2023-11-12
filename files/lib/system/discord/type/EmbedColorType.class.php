<?php

namespace wcf\system\discord\type;

use wcf\system\exception\UserInputException;
use wcf\system\Regex;
use wcf\system\WCF;

class EmbedColorType extends AbstractDiscordType
{
    /**
     * @inheritDoc
     */
    public function getFormElement($value)
    {
        $value = $this->generateRgbaByDec($value);

        return WCF::getTPL()->fetch('discordEmbedColorOptionType', 'wcf', [
            'optionName' => $this->optionName,
            'value' => $value,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validate($newValue)
    {
        if ($newValue !== '') {
            $regex = new Regex('rgba\(\d{1,3}, \d{1,3}, \d{1,3}, (1|1\.00?|0|0?\.[0-9]{1,2})\)');

            if (!$regex->match($newValue)) {
                throw new UserInputException($this->optionName);
            }
        }
    }

    public function getData($newValue)
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

    public function generateRgbaByDec($value)
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

        return $value;
    }
}
