<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_roles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('vendor_id')->nullable()->constrained();
            $t->foreignId('branch_id')->nullable()->constrained();
            $t->string('name');
            $t->string('key');
            $t->string('scope')->default('tenant');
            $t->json('permissions');
            $t->json('denied_permissions')->nullable();
            $t->unique(['tenant_id', 'key']);
            $t->timestamps();
        });
        Schema::create('role_assignments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('user_id')->constrained();
            $t->foreignId('access_role_id')->constrained();
            $t->unique(['user_id', 'access_role_id']);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_assignments');
        Schema::dropIfExists('access_roles');
    }
};
