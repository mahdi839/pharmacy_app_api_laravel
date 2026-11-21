@extends('layouts.admin')

@section('section_label', 'App Update')
@section('section_hint', 'Tell customers on older app versions to update from the Play Store.')

@section('content')
    <div class="toolbar">
        <div>
            <h1 class="title">App Update</h1>
            <p class="subtitle">Customers whose app is older than the latest version see an "Update Available" popup.</p>
        </div>
    </div>

    @if (session('status'))
        <div class="notice">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="errors">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="notice">
        <strong>After every new Play Store release:</strong> once the new version is live on Google Play,
        set <strong>Latest Version</strong> to that version number (for example 1.2.0) and save.
        Do this only after Google has published it, otherwise customers are sent to a Play Store page without the update.
    </div>

    <form class="form" method="POST" action="{{ route('app-update.update') }}">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="field">
                <label for="latest_version">Latest Version</label>
                <input id="latest_version" name="latest_version" value="{{ old('latest_version', $settings->latestVersion()) }}" required placeholder="1.2.0">
                <div class="muted">Older apps see an "Update Available" popup with a "Later" button.</div>
            </div>

            <div class="field">
                <label for="minimum_version">Minimum Version (required update)</label>
                <input id="minimum_version" name="minimum_version" value="{{ old('minimum_version', $settings->minimumVersion()) }}" required placeholder="1.0.0">
                <div class="muted">Apps older than this must update: the popup has no "Later" button. Keep it low unless an old version must stop working.</div>
            </div>

            <div class="field field-full">
                <label for="message">Update Message</label>
                <textarea id="message" name="message" maxlength="500" placeholder="{{ \App\Services\AppUpdateSettings::DEFAULT_MESSAGE }}">{{ old('message', \App\Models\AppSetting::getValue('app_update_message')) }}</textarea>
                <div class="muted">Leave empty to use the default message.</div>
            </div>

            <div class="field">
                <label for="play_store_url">Play Store Link</label>
                <input id="play_store_url" name="play_store_url" type="url" value="{{ old('play_store_url', \App\Models\AppSetting::getValue('app_play_store_url')) }}" placeholder="{{ \App\Services\AppUpdateSettings::DEFAULT_PLAY_STORE_URL }}">
                <div class="muted">The "Update" button opens the Play Store app on this page. Leave empty for the default.</div>
            </div>

            <div class="field">
                <label for="download_url">Download Link (fallback)</label>
                <input id="download_url" name="download_url" type="url" value="{{ old('download_url', \App\Models\AppSetting::getValue('app_download_url')) }}" placeholder="{{ url(config('services.apk_download_url')) }}">
                <div class="muted">Opened instead when the phone has no Play Store. Leave empty to use the website's APK download.</div>
            </div>

            <div class="field field-full" style="border-top: 1px solid #e3e8ef; padding-top: 14px;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="hidden" name="block_old_app" value="0">
                    <input type="checkbox" name="block_old_app" value="1" style="width: 18px; height: 18px;" @checked(old('block_old_app', $settings->blockOldApp()))>
                    Ask customers on the first Play Store version (1.0.0) to update
                </label>
                <div class="muted">
                    Version 1.0.0 has no update popup. When this is on, that version shows the message below on screen
                    instead of products and cannot be used until the customer updates.
                    Turn it on only after the new version is live on Google Play.
                </div>
            </div>

            <div class="field field-full">
                <label for="old_app_message">Message for version 1.0.0</label>
                <input id="old_app_message" name="old_app_message" maxlength="300" value="{{ old('old_app_message', \App\Models\AppSetting::getValue('app_old_app_message')) }}" placeholder="{{ \App\Services\AppUpdateSettings::DEFAULT_OLD_APP_MESSAGE }}">
            </div>

            <div class="field field-full">
                <button class="btn btn-primary" type="submit">Save</button>
            </div>
        </div>
    </form>
@endsection
