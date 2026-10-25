<?php

namespace App\Domain;

use App\Models\Tenant;
use App\Models\TenantModule;
use Database\Seeders\ContentSeeder;

final class TenantSetup
{
    public function seedContent(Tenant $tenant): void
    {
        $context = app(TenantContext::class);
        $previous = null;
        try {
            $previous = $context->get();
        } catch (\LogicException) {
        }
        $context->set($tenant);
        try {
            app(ContentSeeder::class)->run();
        } finally {
            $previous ? $context->set($previous) : $context->clear();
        }
    }

    public function initialize(Tenant $tenant, bool $enableNew = true): void
    {
        $context = app(TenantContext::class);
        $old = null;
        try {
            $old = $context->get();
        } catch (\LogicException) {
        }
        $context->set($tenant);
        try {
            foreach (config('marketplace.modules') as $key => $module) {
                TenantModule::firstOrCreate(['key' => $key], ['enabled' => $enableNew]);
            }
        } finally {
            $old ? $context->set($old) : $context->clear();
        }
    }
}
