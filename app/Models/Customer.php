<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

#[Hidden(['password', 'api_token'])]
class Customer extends Model
{
    protected $fillable = [
        'name',
        'gmail',
        'phone',
        'address',
        'password',
        'api_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'notifications_seen_at' => 'datetime',
        ];
    }

    /**
     * Notifications created after this moment are unread. A new customer does not
     * see notifications sent before they registered as unread.
     */
    public function notificationsUnreadAfter(): Carbon
    {
        return $this->notifications_seen_at && $this->notifications_seen_at->greaterThan($this->created_at)
            ? $this->notifications_seen_at
            : $this->created_at;
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function refreshApiToken(): string
    {
        $token = Str::random(64);

        $this->forceFill(['api_token' => $token])->save();

        return $token;
    }

    public function apiPayload(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'gmail' => $this->gmail,
            'phone' => $this->phone,
            'address' => $this->address,
        ];
    }
}
