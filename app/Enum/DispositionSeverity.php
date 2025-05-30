<?php

namespace App\Enum;

enum DispositionSeverity: string
{
    case Normal = 'normal';
    case Important = 'important';
    case Urgent = 'urgent';
    case Immediate = 'immediate';
    case Confidential = 'confidential';
    case TopSecret = 'top_secret';

    public static function values(): array
    {
        return array_map(fn (self $enum): string => $enum->value, self::cases());
    }
}
