<?php

return [
    'is_available' => !empty(env('GEMINI_API_KEY')),
    'api_key' => env('GEMINI_API_KEY'),
];
