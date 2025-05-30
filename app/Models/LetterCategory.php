<?php

namespace App\Models;

use App\Traits\Models\Filterable;
use App\Traits\Models\Paginate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LetterCategory extends Model
{
    use Paginate;
    use Filterable;

    protected $fillable = [
        'code', 'name', 'description',
    ];

    public function incoming_letters(): BelongsToMany
    {
        return $this->belongsToMany(IncomingLetter::class, 'incoming_letter_category');
    }
}
