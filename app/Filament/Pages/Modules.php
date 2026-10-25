<?php

namespace App\Filament\Pages;

use App\Domain\AccessManager;
use App\Domain\ModuleManager;
use App\Models\PlatformModule;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Modules extends Page
{
    public string $search = '';

    protected string $view = 'filament.pages.modules';

    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    public static function canAccess(): bool
    {
        return auth()->check() && app(AccessManager::class)->allows(auth()->user(), 'modules.manage');
    }

    public function toggle(string $key): void
    {
        abort_unless(static::canAccess(), 403);
        app(ModuleManager::class)->toggle($key, ! app(ModuleManager::class)->enabled($key));
        Notification::make()->title('Module updated')->success()->send();
    }

    public function togglePlatform(string $key): void
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);
        app(ModuleManager::class)->togglePlatform($key, ! (PlatformModule::where('key', $key)->value('enabled') ?? true));
    }
}
