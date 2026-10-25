<?php

namespace App\Console\Commands;

use App\Domain\TenantContext;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\DemoSubscriptionSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpgradeDemo extends Command
{
    protected $signature = 'marketplace:demo-additions';

    protected $description = 'Add missing subscription examples to an existing local demo without replacing commerce records';

    public function handle(): int
    {
        if (! app()->environment('local', 'testing') || ! config('marketplace.demo') || ! config('marketplace.sandbox')) {
            $this->error('Restricted to local/testing demo sandbox installations.');

            return self::FAILURE;
        }
        foreach (['atlas' => 'owner@demo.test', 'harbor' => 'owner2@demo.test'] as $slug => $email) {
            $tenant = Tenant::where('slug', $slug)->first();
            if (! $tenant || ! User::where('tenant_id', $tenant->id)->where('email', $email)->where('role', 'owner')->exists()) {
                continue;
            }
            app(TenantContext::class)->set($tenant);
            try {
                DB::transaction(fn () => app(DemoSubscriptionSeeder::class)->run());
            } finally {
                app(TenantContext::class)->clear();
            }
        }
        $this->info('Missing demo subscriptions added; existing subscriptions and commerce records preserved.');

        return self::SUCCESS;
    }
}
