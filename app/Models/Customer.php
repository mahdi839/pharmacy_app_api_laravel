<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Hidden(['password', 'api_token'])]
class Customer extends Model
{
    protected $fillable = [
        'name',
        'gmail',
        'phone',
        'password',
        'api_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
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
        ];
    }
}
