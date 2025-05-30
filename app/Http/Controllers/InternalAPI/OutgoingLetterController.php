<?php

namespace App\Http\Controllers\InternalAPI;

use setasign\Fpdi\Fpdi;
use Illuminate\Http\Request;
use App\Helpers\GeminiHelper;
use App\Models\LetterCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class OutgoingLetterController extends Controller
{
    public function extract(Request $request): JsonResponse
    {
        try {
            if (!$request->hasFile('file'))
                throw new \Exception('File not found.');

            $file = $request->file('file');
            $mime = $file->getMimeType();
            $extension = $file->getClientOriginalExtension();
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            if (str_contains($mime, 'pdf')) {
                $tempPath = storage_path("app/{$originalName}_2pages.pdf");
                $this->cropPdfToFirstPages($file->getRealPath(), $tempPath);
            } else {
                // jika gambar, cukup copy file ke tempPath
                $tempPath = storage_path("app/{$originalName}.{$extension}");
                copy($file->getRealPath(), $tempPath);
            }

            $base64 = base64_encode(file_get_contents($tempPath));
            $user = $request->user();
            $locale = [
                'id' => 'Indonesia',
                'en' => 'Inggris',
                'zh-CN' => 'China',
                'ar' => 'Arab',
                'ja' => 'Jepang',
                'ko' => 'Korea',
            ];
            $prompt = <<<EOD
                Sebutkan informasi berupa nomor_surat, tanggal_surat, penerima, perihal, body_surat, dan summary dari yang ada di file terlampir!
                Jawab dalam bentuk json dengan key dan value secara berurutan. Tanggal surat harus dalam format mm/dd/yyyy.
                jangan beri code highlight, jangan diberi break point atau enter, value menggunakan bahasa {$locale[$user->locale]}
                dan jangan beri caption apa-apa!
            EOD;

            $response = GeminiHelper::generateTextFromTextAndImageInput($prompt, $mime, $base64);
            Log::debug(sprintf('GEMINI RESPONSE FROM %s@%s', self::class, 'extract'), $response);
            $content = $response['candidates'][0]['content']['parts'][0]['text'];
            return response()->json([
                'error' => false,
                'message' => 'Success',
                'data' => json_decode(GeminiHelper::cleanResult($content), true)
            ]);
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return response()->json([
                'error' => true,
                'message' => $exception->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function category(Request $request): JsonResponse
    {
        try {
            if (!$request->hasFile('file'))
                throw new \Exception('File not found.');

            $categories = implode(', ', LetterCategory::query()->pluck('name')->toArray());

            $file = $request->file('file');
            $mime = $file->getMimeType();
            $base64 = base64_encode(file_get_contents($file->getRealPath()));
            $prompt = <<<EOD
                Aku ingin kamu menjawab dalam bentuk json array seperti ini { "existing_category": [], "new_category": [] }
                Dari surat terlampir aku ingin kamu membantuku untuk klasifikasi surat ke dalam kategori yang cocok,
                jika ada di kategori/klasifikasi berikut [$categories] yang sesuai maka masukkan ke dalam existing_category.
                Jika kamu punya saran kategori/klasifikasi surat lain yang lebih sesuai, maka masukkan ke dalam new_category.
                jangan beri code highlight, jangan diberi break point atau enter
                dan jangan beri caption apa-apa!
            EOD;

            $response = GeminiHelper::generateTextFromTextAndImageInput($prompt, $mime, $base64);
            Log::debug(sprintf('GEMINI RESPONSE FROM %s@%s', self::class, 'category'), $response);
            $content = $response['candidates'][0]['content']['parts'][0]['text'];
            return response()->json([
                'error' => false,
                'message' => 'Success',
                'data' => json_decode(GeminiHelper::cleanResult($content), true)
            ]);
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return response()->json([
                'error' => true,
                'message' => $exception->getMessage(),
                'data' => []
            ], 500);
        }
    }

        function cropPdfToFirstPages($originalPath, $outputPath, $pageLimit = 2)
    {
        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($originalPath);
        $limit = min($pageCount, $pageLimit);

        for ($page = 1; $page <= $limit; $page++) {
            $templateId = $pdf->importPage($page);
            $size = $pdf->getTemplateSize($templateId);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
        }

        $pdf->Output($outputPath, 'F');
    }
}
