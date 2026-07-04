<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    protected $fillable = [
        'image',
    ];

    public function displayImage(): string
    {
        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        return rtrim(request()->getSchemeAndHttpHost(), '/').Storage::url($this->image);
    }

    public function apiPayload(): array
    {
        return [
            'id' => $this->id,
            'image' => $this->displayImage(),
        ];
    }
}
