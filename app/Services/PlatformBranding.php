<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PlatformBranding
{
    private const PLATFORM_LOGO_PATH_KEY = 'platform_logo_path';

    public function viewData(): array
    {
        return [
            'name' => (string) config('branding.platform_name'),
            'initials' => (string) config('branding.platform_initials'),
            'logo_path' => $this->logoPath(),
            'logo_url' => $this->logoUrl(),
        ];
    }

    public function logoPath(): string
    {
        if (! $this->settingsTableExists()) {
            return '';
        }

        return trim((string) AppSetting::query()
            ->where('key', self::PLATFORM_LOGO_PATH_KEY)
            ->value('value'));
    }

    public function logoUrl(): string
    {
        $path = $this->logoPath();

        if ($path === '') {
            return '';
        }

        $lower = strtolower($path);
        if (str_starts_with($lower, 'http://') || str_starts_with($lower, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return url($path);
        }

        return Storage::url($path);
    }

    public function setLogoPath(?string $path): void
    {
        if (! $this->settingsTableExists()) {
            return;
        }

        $normalizedPath = trim((string) $path);

        if ($normalizedPath === '') {
            AppSetting::query()
                ->where('key', self::PLATFORM_LOGO_PATH_KEY)
                ->delete();

            return;
        }

        AppSetting::query()->updateOrCreate(
            ['key' => self::PLATFORM_LOGO_PATH_KEY],
            ['value' => $normalizedPath],
        );
    }

    private function settingsTableExists(): bool
    {
        static $exists = null;

        if ($exists === null) {
            $exists = Schema::hasTable('app_settings');
        }

        return $exists;
    }
}
