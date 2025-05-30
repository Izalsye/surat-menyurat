<?php

namespace App\Traits;

trait HasSingkatanJabatan
{
    public function singkatJabatan($text) {
        $kataSambung = ['dan', 'yang', 'kepada', 'untuk', 'dengan', 'serta'];
        $words = explode(' ', strtolower($text));
        $singkatan = '';

        foreach ($words as $word) {
            if (!in_array($word, $kataSambung) && strlen($word) > 0) {
                $singkatan .= strtoupper($word[0]);
            }
        }

        return $singkatan;
    }
}
