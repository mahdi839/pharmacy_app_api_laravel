<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Services\AppUpdateSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppUpdateController extends Controller
{
    private const VERSION_RULE = 'regex:/^\d+(\.\d+){0,2}$/';

    public function __construct(private AppUpdateSettings $settings) {}

    public function edit(): View
    {
        return view('app-update.edit', ['settings' => $this->settings]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'latest_version' => ['required', 'string', 'max:20', self::VERSION_RULE],
            'minimum_version' => ['required', 'string', 'max:20', self::VERSION_RULE],
            'message' => ['nullable', 'string', 'max:500'],
            'play_store_url' => ['nullable', 'url', 'max:500'],
            'download_url' => ['nullable', 'url', 'max:500'],
            'block_old_app' => ['nullable', 'boolean'],
            'old_app_message' => ['nullable', 'string', 'max:300'],
        ], [
            'latest_version.regex' => 'Latest version must look like 1.2.0.',
            'minimum_version.regex' => 'Minimum version must look like 1.2.0.',
        ]);

        if (version_compare($validated['minimum_version'], $validated['latest_version'], '>')) {
            return back()
                ->withInput()
                ->withErrors(['minimum_version' => 'Minimum version cannot be higher than the latest version.']);
        }

        AppSetting::putMany([
            'app_latest_version' => $validated['latest_version'],
            'app_minimum_version' => $validated['minimum_version'],
            'app_update_message' => $validated['message'] ?? null,
            'app_play_store_url' => $validated['play_store_url'] ?? null,
            'app_download_url' => $validated['download_url'] ?? null,
            'app_block_old_app' => $request->boolean('block_old_app') ? '1' : '0',
            'app_old_app_message' => $validated['old_app_message'] ?? null,
        ]);

        return redirect()
            ->route('app-update.edit')
            ->with('status', 'App update settings saved.');
    }

    public function apiShow(): JsonResponse
    {
        return response()->json(['data' => $this->settings->apiPayload()]);
    }
}
