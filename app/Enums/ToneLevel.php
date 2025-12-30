<?php

namespace App\Enums;

use Spatie\Enum\Enum;

/**
 * @method static self SIMPLE()
 * @method static self OFFICIAL()
 * @method static self VERY_OFFICIAL()
 */
class ToneLevel extends Enum
{
    protected static function values(): array
    {
        return [
            'SIMPLE' => 'simple',
            'OFFICIAL' => 'official',
            'VERY_OFFICIAL' => 'very_official',
        ];
    }

    protected static function labels(): array
    {
        return [
            'SIMPLE' => 'Simple',
            'OFFICIAL' => 'Officiel',
            'VERY_OFFICIAL' => 'Très Officiel',
        ];
    }
}