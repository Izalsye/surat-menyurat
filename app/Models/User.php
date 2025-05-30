<?php

namespace App\Models;

use App\Traits\Models\Filterable;
use App\Traits\Models\Paginate;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    use Filterable, Paginate;
    use HasRoles, LogsActivity;
    use HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'locale',
        'signature_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected $appends = [
        'avatar',
        'signature_url',
    ];

    public function getAvatarAttribute(): string
    {
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF&background=EBF4FF';
    }

    public function getSignatureUrlAttribute(): ?string
    {
        if ($this->signature_path && Storage::disk('public')->exists($this->signature_path)) {
            return Storage::disk('public')->url($this->signature_path);
        }
        return null;
    }

    public function readLetters(): BelongsToMany
    {
        return $this->belongsToMany(IncomingLetter::class)
            ->withPivot('read_at')
            ->withTimestamps();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName(self::class)
            ->logOnly(['name', 'email'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
                'created' => __('activity.created', [
                    'menu' => __('menu.user'),
                    'identifier' => $this->name,
                    'link' => '#',
                ]),
                'updated' => __('activity.updated', [
                    'menu' => __('menu.user'),
                    'identifier' => $this->name,
                    'link' => '#',
                ]),
                'deleted' => __('activity.deleted', [
                    'menu' => __('menu.user'),
                    'identifier' => $this->name,
                ]),
            });
    }
}
