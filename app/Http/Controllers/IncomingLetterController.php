<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Str;
use App\Enum\IncomingStatus;
use Illuminate\Http\Request;
use App\Models\IncomingLetter;
use App\Models\LetterCategory;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use App\Jobs\GenerateIncomingLetterReport;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use App\Http\Requests\IncomingLetter\StoreRequest;
use App\Http\Requests\IncomingLetter\UpdateRequest;

class IncomingLetterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        return Inertia::render('incoming_letter/Index', [
            'items' => IncomingLetter::with([
                'readers' => fn($query) => $query->where('user_id', $user->id),
                'letter_categories',
                'dispositions',
                'replies',
            ])
                ->latest()
                ->filter($request->query(), $user->id)
                ->render($request->query('size')),
            'categories' => LetterCategory::all(),
            'statuses' => IncomingStatus::values(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        try {
            $user = $request->user(); // Get user
            $input = $request->validated();

            if ($input['letter_date']) {
                $input['letter_date'] = Carbon::parse($input['letter_date']);
            }
            if ($input['created_at']) {
                $input['created_at'] = Carbon::parse($input['created_at'])->setTimezone('Asia/Jakarta');
            }

            $uploadedFile = $request->file('file');
            $mime = $uploadedFile->getMimeType();

            if (Str::startsWith($mime, 'image')) {
                $manager = new ImageManager(new GdDriver());
                $uploadedImage = $manager->read($request->file('file')->getPathname());

                $kopPath = storage_path('app/public/kop_surat.png');
                if (file_exists($kopPath)) {
                    $kopImage = $manager->read($kopPath)->resize($uploadedImage->width());
                    $uploadedImage->place($kopImage, 'top');
                }

                if ($request->boolean('sign_letter') && $user->signature_path && Storage::disk('public')->exists($user->signature_path)) {
                    $signatureImage = $manager->read(Storage::disk('public')->path($user->signature_path));
                    // Adjust size/position as needed
                    $signatureImage->resize(250, null, function ($constraint) { // Increased size for visibility
                        $constraint->aspectRatio();
                    });
                    $uploadedImage->place($signatureImage, 'bottom-right', 20, 20); // Increased padding
                }

                $filename = 'incoming_letter/' . uniqid() . '.jpg';
                Storage::disk('public')->put($filename, (string) $uploadedImage->toJpeg()); // Ensure 'public' disk

                $input['file'] = $filename;
            } elseif ($uploadedFile->getClientOriginalExtension() === 'pdf') {
                $pdf = new Fpdi();
                $sourcePath = $uploadedFile->getPathname();
                Log::info($sourcePath);
                $pageCount = $pdf->setSourceFile($sourcePath);

                // Path tanpa 'public' di storage/app/incoming_letter
                $relativePath = 'incoming_letter/' . uniqid() . '.pdf';
                $outputPath = storage_path('app/public/' . $relativePath);

                // Pastikan folder tujuan ada (buat kalau belum ada)
                $dir = dirname($outputPath);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }

                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $templateId = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($templateId);

                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($templateId);

                    if ($pageNo === 1) {
                        $kopPath = storage_path('app/public/kop_surat.png');
                        if (file_exists($kopPath)) {
                            $pdf->Image($kopPath, 10, 5, $size['width'] - 20);
                        }

                        // Add signature if requested
                        if ($request->boolean('sign_letter') && $user->signature_path && Storage::disk('public')->exists($user->signature_path)) {
                            $sigPath = Storage::disk('public')->path($user->signature_path);
                            try {
                                $imgInfo = getimagesize($sigPath);
                                if ($imgInfo) {
                                    $pageWidth = $size['width'];
                                    $pageHeight = $size['height'];
                                    $signatureFileWidth = $imgInfo[0];
                                    $signatureFileHeight = $imgInfo[1];
                                    $aspectRatio = $signatureFileWidth / $signatureFileHeight;
                                    
                                    $displaySignatureWidth = 40; // mm, desired display width of signature
                                    $displaySignatureHeight = $displaySignatureWidth / $aspectRatio;

                                    // Position: bottom-right
                                    $sigX = $pageWidth - $displaySignatureWidth - 15; // 15mm from right
                                    $sigY = $pageHeight - $displaySignatureHeight - 15; // 15mm from bottom

                                    $pdf->Image($sigPath, $sigX, $sigY, $displaySignatureWidth);
                                }
                            } catch (\Exception $e) {
                                Log::error("Error getting signature image size or placing signature: " . $e->getMessage());
                                // Optionally, inform the user or skip placing signature
                            }
                        }
                    }
                }

                $pdf->Output('F', $outputPath);
                $input['file'] = $relativePath;
            } else {
                // Default simpan langsung, ensure it uses public disk if accessible via URL later
                $input['file'] = $uploadedFile->store('incoming_letter', 'public');
            }

            $input['created_by'] = $user->id; // Use the $user variable
            $letter = IncomingLetter::query()->create($input);

            if ($codes = $request->input('categories')) {
                $categories = LetterCategory::query()
                    ->whereIn('code', $codes)
                    ->pluck('id')->toArray();
                $letter->letter_categories()->attach($categories);
            }
            Log::info('Incoming letter create successfully.', [
                'id' => $letter->id,
                'data' => $input,
                'user_id' => $request->user()->id,
            ]);

            return back()->with('success', __('action.created', ['menu' => __('menu.incoming_letter')]));
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
            return back()->with('error', $exception->getMessage());
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(IncomingLetter $letter): Response
    {
        $user = auth()->user();
        $alreadyRead = $letter->readers()->where('user_id', $user->id)->exists();
        if (!$alreadyRead) {
            $letter->readers()->attach($user->id, ['read_at' => now()]);
        }

        $letter->load([
            'letter_categories',
            'replies',
            'dispositions' => fn($query) => $query->whereNull('parent_id')->orderByDesc('created_at'),
            'dispositions.assigner',
            'dispositions.assignee',
            'dispositions.replies',
            'dispositions.children',
        ]);

        return Inertia::render('incoming_letter/Show', [
            'item' => $letter,
            'next' => IncomingLetter::query()
                ->where('created_at', '<', $letter->created_at)
                ->orderByDesc('created_at')
                ->limit(1)
                ->value('id'),
            'prev' => IncomingLetter::query()
                ->where('created_at', '>', $letter->created_at)
                ->orderBy('created_at')
                ->limit(1)
                ->value('id'),
            'total' => IncomingLetter::query()->count('id'),
            'index' => IncomingLetter::query()
                ->where('created_at', '>', $letter->created_at)
                ->orderByDesc('created_at')
                ->count('id') + 1,
            'categories' => LetterCategory::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, IncomingLetter $letter): RedirectResponse
    {
        try {
            $input = $request->validated();

            if ($input['letter_date']) {
                $input['letter_date'] = Carbon::parse($input['letter_date']);
            }
            if ($input['created_at']) {
                $input['created_at'] = Carbon::parse($input['created_at'])->setTimezone('Asia/Jakarta');
            }
            $letter->update($input);
            if ($codes = $request->input('categories')) {
                $categories = LetterCategory::query()
                    ->whereIn('code', $codes)
                    ->pluck('id')->toArray();
                $letter->letter_categories()->sync($categories);
            }
            Log::info('Incoming letter updated successfully.', [
                'id' => $letter->id,
                'data' => $input,
                'user_id' => $request->user()->id,
            ]);

            return back()->with('success', __('action.updated', ['menu' => __('menu.incoming_letter')]));;
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, IncomingLetter $letter): RedirectResponse
    {
        try {
            $nextId = $request->input('prev') ?? $request->input('next');
            $letter->delete();
            if ($nextId) {
                return redirect()
                    ->route('incoming-letter.show', $nextId)
                    ->with('success', __('action.deleted', ['menu' => __('menu.incoming_letter')]));
            }
            return redirect()->route('incoming-letter.index')
                ->with('success', __('action.deleted', ['menu' => __('menu.incoming_letter')]));
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function massDestroy(Request $request): RedirectResponse
    {
        try {
            $ids = $request->input('ids');
            if (empty($ids)) {
                throw new \Exception('Empty ids');
            }

            $files = IncomingLetter::query()
                ->whereIn('id', $ids)
                ->pluck('file')->toArray();
            foreach ($files as $file) {
                Storage::delete($file);
            }

            IncomingLetter::query()
                ->whereIn('id', $ids)
                ->delete();

            return back()->with('success', __('action.deleted', ['menu' => __('menu.incoming_letter')]));
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }

    public function markAsUnread(Request $request, IncomingLetter $letter): RedirectResponse
    {
        try {
            $user = $request->user();
            $letter->readers()
                ->detach($user->id);

            if ($request->boolean('index')) {
                return redirect()->route('incoming-letter.index');
            }

            return back();
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }

    public function massMarkAsUnread(Request $request): RedirectResponse
    {
        try {
            $ids = $request->input('ids');
            if (empty($ids)) {
                throw new \Exception('Empty ids');
            }

            $user = $request->user();
            $user->readLetters()
                ->detach($ids);

            return back();
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }

    public function export(Request $request): RedirectResponse
    {
        try {
            $input = $request->all();
            $user = $request->user();

            GenerateIncomingLetterReport::dispatch($user, $input);

            return back()->with('success', __('action.report'));
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
            return back()->with('error', $exception->getMessage());
        }
    }
}
