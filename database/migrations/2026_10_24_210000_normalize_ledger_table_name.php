<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ledger_entrys') && ! Schema::hasTable('ledger_entries')) {
            Schema::rename('ledger_entrys', 'ledger_entries');
        }
    }

    public function down(): void {}
};
