<?php

namespace App\Jobs;

use App\Models\AppNotification;
use App\Models\DeviceToken;
use App\Services\FcmService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Pushes an AppNotification to every logged-in customer's phone.
 */
class SendAppNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 600;

    public function __construct(public AppNotification $notification) {}

    public function handle(FcmService $fcm): void
    {
        $notification = $this->notification->fresh(['product']);

        if (! $notification) {
            return;
        }

        $notification->update(['status' => AppNotification::STATUS_SENDING]);

        $sent = 0;
        $failed = 0;

        try {
            $data = array_filter([
                'notification_id' => (string) $notification->id,
                'product_id' => $notification->product_id ? (string) $notification->product_id : null,
            ]);

            DeviceToken::query()
                ->whereNotNull('customer_id')
                ->select(['id', 'token'])
                ->chunkById(500, function ($deviceTokens) use ($fcm, $notification, $data, &$sent, &$failed): void {
                    $result = $fcm->sendToTokens(
                        $deviceTokens->pluck('token')->all(),
                        $notification->title,
                        $notification->body,
                        $notification->imageUrl(),
                        $data,
                    );

                    $sent += $result['sent'];
                    $failed += $result['failed'];

                    if ($result['invalid_tokens'] !== []) {
                        DeviceToken::whereIn(
                            'token_hash',
                            array_map([DeviceToken::class, 'hashToken'], $result['invalid_tokens']),
                        )->delete();
                    }
                });

            $notification->update([
                'status' => $sent === 0 && $failed > 0 ? AppNotification::STATUS_FAILED : AppNotification::STATUS_SENT,
                'sent_count' => $sent,
                'failed_count' => $failed,
                'sent_at' => now(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Sending app notification failed', [
                'notification_id' => $notification->id,
                'error' => $exception->getMessage(),
            ]);

            $notification->update([
                'status' => AppNotification::STATUS_FAILED,
                'sent_count' => $sent,
                'failed_count' => $failed,
                'sent_at' => now(),
            ]);
        }
    }
}
