<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\BranchPanelProvider;
use App\Providers\Filament\TenantPanelProvider;
use App\Providers\Filament\VendorPanelProvider;

return [
    TenantPanelProvider::class,
    VendorPanelProvider::class,
    BranchPanelProvider::class,
    AppServiceProvider::class,
    AdminPanelProvider::class,
];
