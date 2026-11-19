<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class AppNotification extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_SENDING = 'sending';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'title',
        'body',
        'image',
        'product_id',
        'status',
        'sent_count',
        'failed_count',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_count' => 'integer',
            'failed_count' => 'integer',
            'sent_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Public image URL: the uploaded image, otherwise the linked product's own image, otherwise null.
     */
    public function imageUrl(): ?string
    {
        if ($this->image) {
            return rtrim(config('app.url'), '/').Storage::url($this->image);
        }

        if ($this->product?->image) {
            return $this->product->displayImage();
        }

        return null;
    }

    public function apiPayload(?Carbon $unreadAfter = null): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'image_url' => $this->imageUrl(),
            'product_id' => $this->product_id,
            'is_unread' => $unreadAfter === null || $this->created_at->greaterThan($unreadAfter),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
