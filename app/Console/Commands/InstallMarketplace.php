<?php

namespace App\Console\Commands;

use App\Domain\TenantContext;
use App\Domain\TenantSetup;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\ContentSeeder;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class InstallMarketplace extends Command
{
    protected $signature = 'marketplace:install {--email=} {--name=Imran Ahmed}';

    protected $description = 'Migrate, initialize a marketplace and securely create the initial administrator';

    public function handle(): int
    {
        if (! config('app.key')) {
            $this->error('Generate APP_KEY before installation.');

            return self::FAILURE;
        }
        if (app()->isProduction() && (config('app.debug') || config('marketplace.demo'))) {
            $this->error('Production requires APP_DEBUG=false and MARKETPLACE_DEMO=false.');

            return self::FAILURE;
        }
        $this->call('migrate', ['--force' => true]);
        $this->call('db:seed', ['--class' => SubscriptionPlanSeeder::class, '--force' => true]);
        $email = $this->option('email') ?: $this->ask('Administrator email');
        $password = $this->secret('New administrator password (minimum 12 characters)');
        $validator = Validator::make(compact('email', 'password'), ['email' => 'required|email|unique:users,email', 'password' => 'required|string|min:12']);
        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }
        DB::transaction(function () use ($email, $password) {
            $tenant = Tenant::firstOrCreate(['slug' => config('marketplace.default_tenant')], ['name' => 'My Marketplace']);
            app(TenantContext::class)->set($tenant);
            app(TenantSetup::class)->initialize($tenant);
            User::create(['name' => $this->option('name'), 'email' => $email, 'password' => Hash::make($password), 'tenant_id' => $tenant->id, 'role' => 'superadmin']);
            $this->call('db:seed', ['--class' => ContentSeeder::class, '--force' => true]);
        });
        $this->info('Marketplace installed. Sign in at /admin. Configure branding, rates, vendors and inventory.');

        return self::SUCCESS;
    }
}
