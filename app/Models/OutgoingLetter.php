<?php

namespace App\Models;

use App\Helpers\DateHelper;
use App\Traits\Models\Filterable;
use App\Traits\Models\Paginate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OutgoingLetter extends Model
{
    use HasUuids;
    use Paginate;
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'letter_number', 'letter_date', 'recipient', 'subject',
        'body', 'summary', 'is_draft', 'file', 'created_by', 'agenda_number',
        'disposition_id', 'incoming_letter_id',
    ];

    protected $appends = [
        'file_url',
    ];

    public function getFileUrlAttribute(): string
    {
        if (filter_var($this->file, FILTER_VALIDATE_URL)) {
            return $this->file;
        }

        return Storage::url($this->file);
    }

    protected static function boot(): void
    {
        parent::boot();
        static::deleting(function (OutgoingLetter $letter) {
            Storage::delete($letter->file);
        });
        static::creating(function (OutgoingLetter $letter) {
            if (empty($letter->agenda_number)) {
                DB::transaction(function () use ($letter) {
                    $now = now();
                    $countThisMonth = DB::table('outgoing_letters')
                            ->whereMonth('created_at', $now)
                            ->whereYear('created_at', $now)
                            ->lockForUpdate()
                            ->count() + 1;
                    $letter->agenda_number = sprintf("K-%s/%s/%s", str_pad($countThisMonth, 3, '0', STR_PAD_LEFT), DateHelper::getRomanMonth(), date('Y'));;
                });
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function letter_categories(): BelongsToMany
    {
        return $this->belongsToMany(LetterCategory::class, 'outgoing_letter_category');
    }

    public function disposition(): BelongsTo
    {
        return $this->belongsTo(Disposition::class);
    }

    public function incoming_letter(): BelongsTo
    {
        return $this->belongsTo(IncomingLetter::class);
    }

    public function scopeFilter(Builder $query, array $filters, ?int $userId = null): void
    {
        $query->when($filters['agenda_number'] ?? null, function (Builder $query, string $agendaNumber) {
            $query->where('agenda_number', $agendaNumber);
        });

        $query->when($filters['letter_number'] ?? null, function (Builder $query, string $letterNumber) {
            $query->where('letter_number', $letterNumber);
        });

        $query->when($filters['recipient'] ?? null, function (Builder $query, string $recipient) {
            $query->where('recipient', 'like', '%' . $recipient . '%');
        });

        $query->when($filters['subject'] ?? null, function (Builder $query, string $subject) {
            $query->where('subject', 'like', '%' . $subject . '%');
        });

        $dateRange = $filters['letter_date'] ?? [];
        $query->when(!empty($dateRange), function (Builder $query) use ($dateRange) {
            $query->whereBetween(DB::raw('DATE(letter_date)'), $dateRange);
        });

        $dateRange = $filters['created_at'] ?? [];
        $query->when(!empty($dateRange), function (Builder $query) use ($dateRange) {
            $query->whereBetween(DB::raw('DATE(created_at)'), $dateRange);
        });

        $availableStatuses = ['reply_letter', 'follow_up_letter'];
        $status = $filters['status'] ?? null;
        $query->when(in_array($status, $availableStatuses), function (Builder $query) use ($status) {
            $query->when($status === 'reply_letter', function (Builder $query) {
                $query->whereHas('incoming_letter');
            });
            $query->when($status === 'follow_up_letter', function (Builder $query) {
                $query->whereHas('disposition');
            });
        });

        $categories = $filters['categories'] ?? [];
        $query->when(!empty($categories), function (Builder $query) use ($categories) {
            $query->whereHas('letter_categories', function (Builder $query) use ($categories) {
                $query->whereIn('letter_categories.code', $categories);
            });
        });
    }
}
