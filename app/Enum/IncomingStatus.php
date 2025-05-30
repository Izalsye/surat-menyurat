<?php

namespace App\Enum;

enum IncomingStatus: string
{
    case Unread = 'unread';
    case Read = 'read';
    case Processed = 'processed';
    case Replied = 'replied';
    case NoDisposition = 'no_disposition';
    case NeedAction = 'need_action';

    public static function values(): array
    {
        return array_map(fn (self $enum): string => $enum->value, self::cases());
    }
}
