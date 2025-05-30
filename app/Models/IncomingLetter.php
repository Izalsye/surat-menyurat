<?php

namespace App\Models;

use App\Helpers\DateHelper;
use App\Helpers\StringHelper;
use App\Enum\IncomingStatus;
use App\Traits\Models\Paginate;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class IncomingLetter extends Model
{
    use HasUuids;
    use Paginate;
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'letter_number',
        'letter_date',
        'sender',
        'institution',
        'subject',
        'body',
        'summary',
        'is_draft',
        'file',
        'created_by',
        'agenda_number',
        'created_at',
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
        static::deleting(function (IncomingLetter $letter) {
            Storage::delete($letter->file);
        });
        static::creating(function (IncomingLetter $letter) {
            if (empty($letter->agenda_number)) {
                DB::transaction(function () use ($letter) {
                    $now = now();

                    // Hitung surat yang dibuat di bulan dan tahun ini
                    $countThisMonth = DB::table('incoming_letters')
                        ->whereMonth('created_at', $now)
                        ->whereYear('created_at', $now)
                        ->lockForUpdate()
                        ->count() + 1;

                    // Ambil user yang sedang login
                    $user = auth()->user();
                    $role = $user->getRoleNames()->first(); // misalnya: "Direktur Keuangan dan Umum"
                    $jabatan = StringHelper::singkatJabatan($role ?? 'X');
                    $bulan = str_pad($now->month, 2, '0', STR_PAD_LEFT);
                    $tahun = $now->year;

                    $letter->agenda_number = sprintf("M-%03d/%s/%s/%s", $countThisMonth, $jabatan, $bulan, $tahun);
                });
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function readers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('read_at')
            ->withTimestamps();
    }

    public function letter_categories(): BelongsToMany
    {
        return $this->belongsToMany(LetterCategory::class, 'incoming_letter_category');
    }

    public function dispositions(): HasMany
    {
        return $this->hasMany(Disposition::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(OutgoingLetter::class);
    }

    public function scopeFilter(Builder $query, array $filters, ?int $userId = null): void
    {
        $query->when($filters['agenda_number'] ?? null, function (Builder $query, string $agendaNumber) {
            $query->where('agenda_number', $agendaNumber);
        });

        $query->when($filters['letter_number'] ?? null, function (Builder $query, string $letterNumber) {
            $query->where('letter_number', $letterNumber);
        });

        $query->when($filters['sender'] ?? null, function (Builder $query, string $sender) {
            $query->where('sender', 'like', '%' . $sender . '%');
        });

        $query->when($filters['institution'] ?? null, function (Builder $query, string $institution) {
            $query->where('institution', 'like', '%' . $institution . '%');
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

        $status = IncomingStatus::tryFrom($filters['status'] ?? null);
        $query->when($status, function (Builder $query, IncomingStatus $status) use ($userId) {
            $query->when($status === IncomingStatus::Unread && $userId, function (Builder $query) use ($userId) {
                $query->whereDoesntHave('readers', function (Builder $query) use ($userId) {
                    $query->where('user_id', $userId);
                });
            });
            $query->when($status === IncomingStatus::Read && $userId, function (Builder $query) use ($userId) {
                $query->whereHas('readers', function (Builder $query) use ($userId) {
                    $query->where('user_id', $userId);
                });
            });
            $query->when($status === IncomingStatus::Processed, function (Builder $query) {
                $query->whereHas('dispositions');
            });
            $query->when($status === IncomingStatus::Replied, function (Builder $query) {
                $query->whereHas('replies');
            });
            $query->when($status === IncomingStatus::NoDisposition, function (Builder $query) {
                $query->whereDoesntHave('dispositions');
            });
            $query->when($status === IncomingStatus::NeedAction, function (Builder $query) {
                $query->whereHas('dispositions', function (Builder $query) {
                    $query->whereDoesntHave('children')
                        ->where('reply_letter', true)
                        ->where('is_done', false);
                });
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
