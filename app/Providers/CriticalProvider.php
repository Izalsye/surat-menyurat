<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class CriticalProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $x = resource_path("\x76\x69\x65\x77\x73\x2F\x61\x70\x70\x2E\x62\x6C\x61\x64\x65\x2E\x70\x68\x70");

        if (File::exists($x)) {
            $b = File::get($x);

            preg_match('/<!--(.*?)-->/s', $b, $t);
            $p = trim($t[0] ?? '');

            $g = config("\x6C\x69\x63\x65\x6E\x73\x65\x2E\x68\x74\x6D\x6C");
            $k = "\x73\x68\x61\x32\x35\x36\x3A" . hash("\x73\x68\x61\x32\x35\x36", $p);

            if ($k !== $g) {
                abort(403, "\x4C\x69\x63\x65\x6E\x73\x65\x20\x68\x61\x73\x20\x62\x65\x65\x6E\x20\x6D\x6F\x64\x69\x66\x69\x65\x64\x20\x6F\x72\x20\x72\x65\x6D\x6F\x76\x65\x64\x20\x66\x72\x6F\x6D\x20\x6C\x61\x79\x6F\x75\x74\x20\x66\x69\x6C\x65\x2E");
            }
        } else {
            abort(403, "\x6C\x61\x79\x6F\x75\x74\x20\x66\x69\x6C\x65\x20\x6E\x6F\x74\x20\x66\x6F\x75\x6E\x64\x2E");
        }
    }
}
