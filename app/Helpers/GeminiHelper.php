<?php

namespace App\Helpers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class GeminiHelper
{
    private static string $baseUrl = 'https://generativelanguage.googleapis.com';

    private static function url(string $endpoint): string
    {
        return sprintf('%s/%s?key=%s', self::$baseUrl, $endpoint, config('gemini.api_key'));
    }

    public static function isAvailable(): bool
    {
        return config('gemini.is_available');
    }

    /**
     * Generate text from text-only input
     * [POST] /v1beta/models/gemini-1.5-flash:generateContent
     * https://ai.google.dev/gemini-api/docs/text-generation?lang=rest#generate-text-from-text
     *
     * @throws ConnectionException
     */
    public static function generateTextFromTextOnlyInput(string $text): array
    {
        $endpoint = 'v1beta/models/gemini-1.5-flash:generateContent';
        $url = self::url($endpoint);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $text],
                    ],
                ],
            ],
        ]);

        return json_decode($response->body(), true);
    }

    /**
     * Generate text from text and image input
     * [POST] /v1beta/models/gemini-1.5-flash:generateContent
     * https://ai.google.dev/gemini-api/docs/text-generation?lang=rest#generate-text-from-text-and-image
     *
     * @throws ConnectionException
     */
    public static function generateTextFromTextAndImageInput(string $text, string $mime, string $base64): array
    {
        $endpoint = 'v1beta/models/gemini-1.5-flash:generateContent';
        $url = self::url($endpoint);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $text],
                        [
                            'inline_data' => [
                                'mime_type' => $mime,
                                'data' => $base64,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        return json_decode($response->body(), true);
    }

    public static function cleanResult(string $input): string
    {
        $clean1 = str_replace('\n', '', $input);
        $clean2 = str_replace("\n", '', $clean1);
        $clean3 = str_replace('```json', '', $clean2);
        $clean4 = str_replace('```', '', $clean3);

        return trim($clean4);
    }

    public static function markdownToHtml(string $markdown): string
    {
        // Convert headers (#, ##, ###)
        $markdown = preg_replace('/^### (.*)/m', '<h3>$1</h3>', $markdown);
        $markdown = preg_replace('/^## (.*)/m', '<h2>$1</h2>', $markdown);
        $markdown = preg_replace('/^# (.*)/m', '<h1>$1</h1>', $markdown);

        // Convert bold text (**bold** or __bold__)
        $markdown = preg_replace('/\*\*(.*)\*\*/U', '<strong>$1</strong>', $markdown);
        $markdown = preg_replace('/__(.*)__/U', '<strong>$1</strong>', $markdown);

        // Convert italic text (*italic* or _italic_)
        $markdown = preg_replace('/\*(.*)\*/U', '<em>$1</em>', $markdown);
        $markdown = preg_replace('/_(.*)_/U', '<em>$1</em>', $markdown);

        // Convert unordered lists (- item)
        $markdown = preg_replace('/^- (.*)/m', '<li>$1</li>', $markdown);
        $markdown = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $markdown);

        // Convert links [text](url)
        $markdown = preg_replace('/\[(.*)\]\((.*)\)/U', '<a href="$2">$1</a>', $markdown);

        // Replace newline characters (\n) with <br />
        $markdown = str_replace("\n", '<br />', $markdown);

        return trim($markdown);
    }
}
