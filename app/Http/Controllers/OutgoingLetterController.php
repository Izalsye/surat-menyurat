<?php

namespace App\Http\Controllers;

use App\Http\Requests\OutgoingLetter\StoreRequest;
use App\Http\Requests\OutgoingLetter\UpdateRequest;
use App\Jobs\GenerateOutgoingLetterReport;
use App\Mail\DispositionDone;
use App\Models\Disposition;
use App\Models\LetterCategory;
use App\Models\OutgoingLetter;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use setasign\Fpdi\Fpdi;

class OutgoingLetterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('outgoing_letter/Index', [
            'items' => OutgoingLetter::with(['letter_categories', 'disposition'])
                ->latest()
                ->filter($request->query())
                ->render($request->query('size')),
            'categories' => LetterCategory::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        try {
            $input = $request->validated();
            $user = $request->user(); // Get authenticated user

            $uploadedFile = $input['file']; // This is an UploadedFile object
            $originalFilePath = $uploadedFile->getPathname();
            $mime = $uploadedFile->getMimeType();
            $finalStoragePath = null;

            $manager = new ImageManager(new GdDriver()); // Initialize ImageManager

            if ($uploadedFile->getClientOriginalExtension() === 'pdf' || $mime === 'application/pdf') {
                $pdf = new Fpdi();
                $pageCount = $pdf->setSourceFile($originalFilePath);
                $relativePath = 'outgoing_letter/' . Str::uuid()->toString() . '.pdf';
                
                // Ensure the output directory exists
                $outputDirectory = dirname(Storage::disk('public')->path($relativePath));
                if (!is_dir($outputDirectory)) {
                    mkdir($outputDirectory, 0755, true);
                }

                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $templateId = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($templateId);
                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($templateId);

                    if ($pageNo === 1 && $request->boolean('sign_letter') && $user->signature_path && Storage::disk('public')->exists($user->signature_path)) {
                        $sigPath = Storage::disk('public')->path($user->signature_path);
                        try {
                            $imgInfo = getimagesize($sigPath);
                            if ($imgInfo) {
                                $pageWidth = $size['width'];
                                $pageHeight = $size['height'];
                                $signatureFileWidth = $imgInfo[0];
                                $signatureFileHeight = $imgInfo[1];
                                $aspectRatio = $signatureFileWidth / $signatureFileHeight;
                                $displaySignatureWidth = 40; // mm
                                $displaySignatureHeight = $displaySignatureWidth / $aspectRatio;
                                $sigX = $pageWidth - $displaySignatureWidth - 15; // 15mm from right
                                $sigY = $pageHeight - $displaySignatureHeight - 15; // 15mm from bottom
                                $pdf->Image($sigPath, $sigX, $sigY, $displaySignatureWidth);
                            }
                        } catch (\Exception $e) {
                            Log::error("Error processing signature for PDF in OutgoingLetter: " . $e->getMessage());
                        }
                    }
                }
                $pdf->Output('F', Storage::disk('public')->path($relativePath)); // Save PDF directly to storage path
                $finalStoragePath = $relativePath;

            } elseif (Str::startsWith($mime, 'image')) {
                $processedImage = $manager->read($originalFilePath);
                if ($request->boolean('sign_letter') && $user->signature_path && Storage::disk('public')->exists($user->signature_path)) {
                    $signatureDiskPath = Storage::disk('public')->path($user->signature_path);
                    $signatureImage = $manager->read($signatureDiskPath);
                    // Resize signature before placing
                    $signatureImage->resize(250, null, function ($constraint) { 
                        $constraint->aspectRatio(); 
                        $constraint->upsize(); // Prevent upsizing if signature is smaller than 250px
                    });
                    $processedImage->place($signatureImage, 'bottom-right', 20, 20);
                }
                
                $imageExtension = $uploadedFile->getClientOriginalExtension() ?: 'jpg'; // Fallback extension
                // Ensure the extension is valid for encoding
                $validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'tif', 'tiff', 'bmp'];
                if (!in_array(strtolower($imageExtension), $validExtensions)) {
                    $imageExtension = 'jpg'; // Default to jpg if extension is unusual
                }
                $relativePath = 'outgoing_letter/' . Str::uuid()->toString() . '.' . $imageExtension;
                Storage::disk('public')->put($relativePath, (string) $processedImage->encodeByExtension($imageExtension));
                $finalStoragePath = $relativePath;

            } else {
                // Default: store original file directly to public disk
                $finalStoragePath = $uploadedFile->store('outgoing_letter', 'public');
            }

            $input['file'] = $finalStoragePath; // Update with the new path string (relative to public disk root)
            
            if ($input['letter_date']) {
                $input['letter_date'] = Carbon::parse($input['letter_date']);
            }
            $input['created_by'] = $user->id;
            
            $letter = OutgoingLetter::query()->create($input);

            if ($codes = $request->input('categories')) {
                $categories = LetterCategory::query()
                    ->whereIn('code', $codes)
                    ->pluck('id')->toArray();
                $letter->letter_categories()->attach($categories);
            }

            if ($request->input('disposition_id')) {
                $disposition = Disposition::query()->find($request->input('disposition_id'));
                if ($disposition) { // Check if disposition exists
                    $disposition->update([
                        'is_done' => true,
                        'done_at' => now(),
                    ]);
                    $assignee = $disposition->assignee;
                    if ($assignee) {
                        Mail::to($assignee->email)
                            ->queue(new DispositionDone($assignee, $disposition, $letter));
                    }
                    $assigner = $disposition->assigner;
                    if ($assigner) { // Check if assigner exists
                        Mail::to($assigner->email)
                            ->queue(new DispositionDone($assigner, $disposition, $letter));
                    }
                }
            }

            return back()->with('success', __('action.created', ['menu' => __('menu.outgoing_letter')]));
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
            // Add stack trace for more detailed logging if possible
            Log::error($exception->getTraceAsString()); 
            return back()->with('error', $exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(OutgoingLetter $letter): Response
    {
        $letter->load([
            'letter_categories', 'incoming_letter', 'disposition',
            'disposition.assigner', 'disposition.assignee',
        ]);

        return Inertia::render('outgoing_letter/Show', [
            'item' => $letter,
            'next' => OutgoingLetter::query()
                ->where('created_at', '<', $letter->created_at)
                ->orderByDesc('created_at')
                ->limit(1)
                ->value('id'),
            'prev' => OutgoingLetter::query()
                ->where('created_at', '>', $letter->created_at)
                ->orderBy('created_at')
                ->limit(1)
                ->value('id'),
            'total' => OutgoingLetter::query()->count('id'),
            'index' => OutgoingLetter::query()
                    ->where('created_at', '>', $letter->created_at)
                    ->orderByDesc('created_at')
                    ->count('id') + 1,
            'categories' => LetterCategory::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, OutgoingLetter $letter): RedirectResponse
    {
        try {
            $input = $request->validated();
            if ($input['letter_date']) {
                $input['letter_date'] = Carbon::parse($input['letter_date']);
            }
            $letter->update($input);
            if ($codes = $request->input('categories')) {
                $categories = LetterCategory::query()
                    ->whereIn('code', $codes)
                    ->pluck('id')->toArray();
                $letter->letter_categories()->sync($categories);
            }

            return back()->with('success', __('action.updated', ['menu' => __('menu.outgoing_letter')]));;
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, OutgoingLetter $letter): RedirectResponse
    {
        try {
            $nextId = $request->input('prev') ?? $request->input('next');
            $letter->delete();
            if ($nextId) {
                return redirect()
                    ->route('outgoing-letter.show', $nextId)
                    ->with('success', __('action.deleted', ['menu' => __('menu.outgoing_letter')]));
            }
            return redirect()->route('outgoing-letter.index')
                ->with('success', __('action.deleted', ['menu' => __('menu.outgoing_letter')]));
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

            $files = OutgoingLetter::query()
                ->whereIn('id', $ids)
                ->pluck('file')->toArray();
            foreach ($files as $file) {
                Storage::delete($file);
            }

            OutgoingLetter::query()
                ->whereIn('id', $ids)
                ->delete();

            return back()->with('success', __('action.deleted', ['menu' => __('menu.outgoing_letter')]));
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

            GenerateOutgoingLetterReport::dispatch($user, $input);

            return back()->with('success', __('action.report'));
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
            return back()->with('error', $exception->getMessage());
        }
    }
}
