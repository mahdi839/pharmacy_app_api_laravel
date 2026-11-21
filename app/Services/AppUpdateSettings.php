<?php

namespace App\Services;

use App\Models\AppSetting;

/**
 * Mobile app update settings, edited on the dashboard's "App Update" page.
 *
 * - latest_version: newest version on the Play Store. Older apps see an optional "Update Available" popup.
 * - minimum_version: apps older than this must update (popup without a "Later" button).
 * - block_old_app: the first Play Store version (1.0.0) has no update popup, so when this is on the API
 *   answers it with an "update the app" message instead of data.
 */
class AppUpdateSettings
{
    public const DEFAULT_PLAY_STORE_URL = 'https://play.google.com/store/apps/details?id=com.medbangladesh.app';

    public const DEFAULT_MESSAGE = 'A new version of Med Bangladesh is available with new features and improvements. Please update the app.';

    public const DEFAULT_OLD_APP_MESSAGE = 'A new version of Med Bangladesh is available. Please update the app from Google Play Store to continue.';

    public function latestVersion(): string
    {
        return AppSetting::getValue('app_latest_version', '1.1.0');
    }

    public function minimumVersion(): string
    {
        return AppSetting::getValue('app_minimum_version', '1.0.0');
    }

    public function message(): string
    {
        return AppSetting::getValue('app_update_message') ?: self::DEFAULT_MESSAGE;
    }

    public function playStoreUrl(): string
    {
        return AppSetting::getValue('app_play_store_url') ?: self::DEFAULT_PLAY_STORE_URL;
    }

    public function downloadUrl(): string
    {
        return AppSetting::getValue('app_download_url') ?: url(config('services.apk_download_url'));
    }

    public function blockOldApp(): bool
    {
        return AppSetting::getValue('app_block_old_app') === '1';
    }

    public function oldAppMessage(): string
    {
        return AppSetting::getValue('app_old_app_message') ?: self::DEFAULT_OLD_APP_MESSAGE;
    }

    /**
     * What the mobile app needs to decide whether to show the update popup.
     */
    public function apiPayload(): array
    {
        return [
            'latest_version' => $this->latestVersion(),
            'minimum_version' => $this->minimumVersion(),
            'message' => $this->message(),
            'play_store_url' => $this->playStoreUrl(),
            'download_url' => $this->downloadUrl(),
        ];
    }
}
