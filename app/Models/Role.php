<?php

namespace App\Models;

use App\Traits\Models\Filterable;
use App\Traits\Models\Paginate;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Role extends \Spatie\Permission\Models\Role
{
    use Filterable, Paginate;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName(self::class)
            ->logOnly(['name'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
                'created' => __('activity.created', [
                    'menu' => __('menu.role'),
                    'identifier' => $this->name,
                    'link' => '#',
                ]),
                'updated' => __('activity.updated', [
                    'menu' => __('menu.role'),
                    'identifier' => $this->name,
                    'link' => '#',
                ]),
                'deleted' => __('activity.deleted', [
                    'menu' => __('menu.role'),
                    'identifier' => $this->name,
                ]),
            });
    }
}
