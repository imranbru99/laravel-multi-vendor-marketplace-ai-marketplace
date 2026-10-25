<?php

namespace App\Filament\Pages;

use App\Domain\AccessManager;
use App\Domain\ModuleManager;
use App\Models\Branch;
use App\Models\Vendor;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ScopedModules extends Page
{
    protected string $view = 'filament.pages.scoped-modules';

    public string $scope = 'vendor';

    public int $subjectId = 0;

    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    public static function canAccess(): bool
    {
        return auth()->check() && (auth()->user()->managesMarketplace() || app(AccessManager::class)->allows(auth()->user(), 'modules.manage_scoped', in_array(auth()->user()->role, ['vendor', 'manager', 'branch', 'warehouse'], true)));
    }

    public function mount(): void
    {
        if (auth()->user()->branch_id) {
            $this->scope = 'branch';
            $this->subjectId = auth()->user()->branch_id;
        } elseif (auth()->user()->vendor_id) {
            $this->subjectId = auth()->user()->vendor_id;
        }
    }

    public function subjects()
    {
        return $this->scope === 'branch' ? Branch::query()->when(auth()->user()->vendor_id, fn ($q) => $q->where('vendor_id', auth()->user()->vendor_id))->when(auth()->user()->branch_id, fn ($q) => $q->whereKey(auth()->user()->branch_id))->pluck('name', 'id') : Vendor::query()->when(auth()->user()->vendor_id, fn ($q) => $q->whereKey(auth()->user()->vendor_id))->pluck('name', 'id');
    }

    public function effective(string $key): bool
    {
        $branch = $this->scope === 'branch' ? Branch::find($this->subjectId) : null;

        return app(ModuleManager::class)->enabled($key, $branch?->vendor_id ?? ($this->subjectId ?: null), $branch?->id);
    }

    public function toggle(string $key): void
    {
        abort_unless(static::canAccess(), 403);
        $this->validate(['scope' => 'required|in:vendor,branch', 'subjectId' => 'required|integer|min:1']);
        app(ModuleManager::class)->toggleScoped($key, $this->scope, $this->subjectId, ! $this->effective($key));
        Notification::make()->title('Scoped module setting updated')->success()->send();
    }
}
