<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    public static function getRomanMonth(Carbon $date = null): string
    {
        $date = $date ?? Carbon::now();
        return match ($date->month) {
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        };
    }
}
