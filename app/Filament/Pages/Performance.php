<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Performance extends Page
{
    protected string $view = 'filament.pages.performance';

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'superadmin';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Platform';
    }
}
