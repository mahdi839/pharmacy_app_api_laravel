<?php

namespace App\Services;

use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Sends push notifications through Firebase Cloud Messaging (HTTP v1 API),
 * authenticating with the Firebase service account JSON key.
 */
class FcmService
{
    private const SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';

    private const CONCURRENCY = 50;

    private ?array $credentials = null;

    public function isConfigured(): bool
    {
        $path = config('services.fcm.credentials');

        return is_string($path) && is_file($path) && is_readable($path);
    }

    /**
     * Sends one notification to many device tokens.
     *
     * @param  array<int, string>  $tokens
     * @param  array<string, string>  $data  Extra key/values delivered to the app (values must be strings).
     * @return array{sent: int, failed: int, invalid_tokens: array<int, string>}
     */
    public function sendToTokens(array $tokens, string $title, string $body, ?string $imageUrl = null, array $data = []): array
    {
        $result = ['sent' => 0, 'failed' => 0, 'invalid_tokens' => []];
        $tokens = array_values(array_unique($tokens));

        if ($tokens === []) {
            return $result;
        }

        $accessToken = $this->accessToken();
        $url = 'https://fcm.googleapis.com/v1/projects/'.$this->credentials()['project_id'].'/messages:send';

        foreach (array_chunk($tokens, self::CONCURRENCY) as $chunk) {
            $responses = Http::pool(fn (Pool $pool) => array_map(
                fn (string $token) => $pool->withToken($accessToken)
                    ->timeout(15)
                    ->post($url, ['message' => $this->message($token, $title, $body, $imageUrl, $data)]),
                $chunk,
            ));

            foreach ($chunk as $index => $token) {
                $response = $responses[$index] ?? null;

                if ($response instanceof Response && $response->successful()) {
                    $result['sent']++;

                    continue;
                }

                $result['failed']++;

                if ($response instanceof Response && $this->isInvalidToken($response)) {
                    $result['invalid_tokens'][] = $token;
                }
            }
        }

        return $result;
    }

    private function message(string $token, string $title, string $body, ?string $imageUrl, array $data): array
    {
        $notification = array_filter([
            'title' => $title,
            'body' => $body,
            'image' => $imageUrl,
        ]);

        return [
            'token' => $token,
            'notification' => $notification,
            'data' => array_map('strval', $data),
            'android' => [
                'priority' => 'high',
                'notification' => [
                    // Must match the channel created in the mobile app.
                    'channel_id' => 'default',
                ],
            ],
        ];
    }

    /**
     * The token no longer belongs to an installed app (uninstalled, data cleared, or expired).
     */
    private function isInvalidToken(Response $response): bool
    {
        if ($response->status() === 404) {
            return true;
        }

        foreach ((array) $response->json('error.details', []) as $detail) {
            if (($detail['errorCode'] ?? null) === 'UNREGISTERED') {
                return true;
            }
        }

        return false;
    }

    private function accessToken(): string
    {
        $cacheKey = 'fcm_access_token_'.md5($this->credentials()['client_email']);

        return Cache::remember($cacheKey, now()->addMinutes(50), function (): string {
            $credentials = $this->credentials();
            $now = time();

            $jwt = $this->signJwt([
                'iss' => $credentials['client_email'],
                'scope' => self::SCOPE,
                'aud' => $credentials['token_uri'],
                'iat' => $now,
                'exp' => $now + 3600,
            ], $credentials['private_key']);

            $response = Http::asForm()->timeout(15)->post($credentials['token_uri'], [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if (! $response->successful() || ! $response->json('access_token')) {
                throw new RuntimeException('Could not get a Firebase access token (HTTP '.$response->status().').');
            }

            return $response->json('access_token');
        });
    }

    private function signJwt(array $claims, string $privateKey): string
    {
        $segments = [
            $this->base64Url(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])),
            $this->base64Url(json_encode($claims)),
        ];

        if (! openssl_sign(implode('.', $segments), $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Could not sign the Firebase access token request.');
        }

        $segments[] = $this->base64Url($signature);

        return implode('.', $segments);
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function credentials(): array
    {
        if ($this->credentials !== null) {
            return $this->credentials;
        }

        if (! $this->isConfigured()) {
            throw new RuntimeException('Firebase credentials file is missing. Expected at: '.config('services.fcm.credentials'));
        }

        $credentials = json_decode((string) file_get_contents(config('services.fcm.credentials')), true);

        foreach (['project_id', 'client_email', 'private_key', 'token_uri'] as $key) {
            if (empty($credentials[$key])) {
                throw new RuntimeException("Firebase credentials file is missing \"{$key}\".");
            }
        }

        return $this->credentials = $credentials;
    }
}
