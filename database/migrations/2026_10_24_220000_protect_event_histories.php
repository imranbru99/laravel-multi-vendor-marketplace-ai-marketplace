<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tables = ['inventory_movements', 'ledger_transactions', 'ledger_entries', 'audit_logs', 'payment_events', 'refunds', 'settlements'];

    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            DB::unprepared("CREATE OR REPLACE FUNCTION marketplace_immutable_event() RETURNS trigger AS $$ BEGIN RAISE EXCEPTION 'Marketplace event history is immutable'; END; $$ LANGUAGE plpgsql");
            foreach ($this->tables as $table) {
                DB::unprepared("CREATE TRIGGER immutable_{$table} BEFORE UPDATE OR DELETE ON {$table} FOR EACH ROW EXECUTE FUNCTION marketplace_immutable_event()");
            }
        } elseif ($driver === 'sqlite') {
            foreach ($this->tables as $table) {
                foreach (['UPDATE', 'DELETE'] as $verb) {
                    $key = strtolower($verb);
                    DB::unprepared("CREATE TRIGGER immutable_{$table}_{$key} BEFORE {$verb} ON {$table} BEGIN SELECT RAISE(ABORT, 'Marketplace event history is immutable'); END");
                }
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (DB::getDriverName() === 'pgsql') {
                DB::unprepared("DROP TRIGGER IF EXISTS immutable_{$table} ON {$table}");
            } elseif (DB::getDriverName() === 'sqlite') {
                foreach (['update', 'delete'] as $verb) {
                    DB::unprepared("DROP TRIGGER IF EXISTS immutable_{$table}_{$verb}");
                }
            }
        }
        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared('DROP FUNCTION IF EXISTS marketplace_immutable_event()');
        }
    }
};
