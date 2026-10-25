<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_modules', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();
            $t->boolean('enabled')->default(true);
            $t->timestamps();
        });
        Schema::create('module_scope_overrides', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->string('key');
            $t->string('scope');
            $t->unsignedBigInteger('subject_id');
            $t->boolean('enabled');
            $t->json('configuration')->nullable();
            $t->unique(['tenant_id', 'key', 'scope', 'subject_id']);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_scope_overrides');
        Schema::dropIfExists('platform_modules');
    }
};
