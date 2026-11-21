<?php

namespace App\Http\Middleware;

use App\Services\AppUpdateSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * App versions from 1.1.0 send an "X-App-Version" header. The first Play Store version (1.0.0)
 * does not, and it has no update popup. When "block old app" is switched on in the dashboard,
 * that old version receives an "update the app" message (which it shows on screen) instead of data.
 */
class BlockOldMobileApp
{
    public function __construct(private AppUpdateSettings $settings) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasHeader('X-App-Version') && $this->settings->blockOldApp()) {
            return response()->json(['message' => $this->settings->oldAppMessage()], 426);
        }

        return $next($request);
    }
}
