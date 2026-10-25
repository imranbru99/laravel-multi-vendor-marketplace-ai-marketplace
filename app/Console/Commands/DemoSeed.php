<?php

namespace App\Console\Commands;

use Database\Seeders\DemoSeeder;
use Illuminate\Console\Command;

class DemoSeed extends Command
{
    protected $signature = 'marketplace:demo-seed';

    protected $description = 'Seed an empty, isolated local/testing database with simulated business data';

    public function handle(): int
    {
        if (! app()->environment('local', 'testing') || ! config('marketplace.demo') || ! config('marketplace.sandbox')) {
            $this->error('Restricted to local/testing with MARKETPLACE_DEMO=true and PAYMENT_SANDBOX=true.');

            return self::FAILURE;
        }
        $this->call('migrate', ['--force' => true]);
        $this->call('db:seed', ['--class' => DemoSeeder::class, '--force' => true]);
        $this->info('Simulated marketplace seeded. Development-only password: Demo@12345');

        return self::SUCCESS;
    }
}
