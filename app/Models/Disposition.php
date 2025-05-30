<?php

namespace App\Models;

use App\Traits\Models\Filterable;
use App\Traits\Models\Paginate;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Disposition extends Model
{
    use Filterable;
    use Paginate;
    use HasUuids;
    use HasFactory;

    protected $fillable = [
        'incoming_letter_id', 'assignee_id', 'assigner_id',
        'description', 'is_done', 'done_at',
        'reply_letter', 'due_at', 'parent_id',
        'urgency'
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'reply_letter' => 'boolean',
        'is_done' => 'boolean',
    ];

    public function incoming_letter(): BelongsTo
    {
        return $this->belongsTo(IncomingLetter::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(OutgoingLetter::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigner_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Disposition::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Disposition::class, 'parent_id')
            ->with(['children', 'assignee', 'assigner', 'replies'])
            ->orderByDesc('created_at');
    }
}
