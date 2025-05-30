<?php

namespace App\Models;

use App\Traits\Models\Filterable;
use App\Traits\Models\Paginate;
use Spatie\Activitylog\Models\Activity;

class ActivityLog extends Activity
{
    use Filterable, Paginate;

    protected $casts = [
        'subject_id' => 'string',
        'properties' => 'array',
    ];

    protected $appends = [
        'module',
    ];

    public function getModuleAttribute(): string
    {
        $class = str_replace('App\Models\\', '', $this->log_name);
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $class));
    }
}
