<?php

namespace App\Jobs;

use App\Mail\ReportExported;
use App\Models\OutgoingLetter;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

class GenerateOutgoingLetterReport implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public User $user, public array $filters)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        App::setLocale($this->user->locale);
        $filter = implode('_', array_map(function ($v) {
            if (is_string($v) && strtotime($v)) {
                return (new \DateTime($v))->format('Y-m-d');
            }
            return $v;
        }, array_filter(iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveArrayIterator($this->filters))), fn($v) => !is_null($v))));
        $filename = sprintf('%s_%s_%s_outgoing-letter-report_%s.csv',
            now()->format('Y-m-d'),
            time(),
            strtolower(str_replace(' ', '-', $this->user->name)),
            empty($filter) ? 'all' : $filter,
        );

        $path = storage_path('app/public/' . $filename);
        $stream = fopen($path, 'w');
        if ($stream === false) {
            if (file_exists($path)) {
                unlink($path);
            }
            die('Cannot open file for writing');
        }

        $header = [
            __('field.agenda_number'), __('field.created_at'),
            __('field.letter_number'), __('field.letter_date'),
            __('field.recipient'), __('field.subject'), __('field.created_by'),
        ];
        fputcsv($stream, $header);

        $letters = OutgoingLetter::with(['user'])
            ->oldest('created_at')
            ->filter($this->filters, $this->user->id)
            ->get();
        foreach ($letters as $letter) {
            fputcsv($stream, [
                $letter->agenda_number,
                Carbon::parse($letter->created_at)->format('Y-m-d'),
                $letter->letter_number ?? '',
                Carbon::parse($letter->letter_date)->format('Y-m-d'),
                $letter->recipient ?? '',
                $letter->subject ?? '',
                $letter->user?->name ?? '',
            ]);
        }

        fclose($stream);

        Mail::to($this->user->email)
            ->send(new ReportExported($this->user, $path));

        if (file_exists($path)) {
            unlink($path);
        }
    }
}
