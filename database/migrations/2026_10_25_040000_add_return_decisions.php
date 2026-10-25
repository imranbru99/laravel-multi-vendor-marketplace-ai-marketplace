<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('return_requests', function (Blueprint $t) {
            $t->text('decision_notes')->nullable();
            $t->timestamp('authorized_at')->nullable();
            $t->timestamp('received_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('return_requests', fn (Blueprint $t) => $t->dropColumn(['decision_notes', 'authorized_at', 'received_at']));
    }
};
