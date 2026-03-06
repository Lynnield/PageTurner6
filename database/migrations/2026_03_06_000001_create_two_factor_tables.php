<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('two_factor_secrets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('method');
            $table->string('secret')->nullable();
            $table->json('recovery_codes')->nullable();
            $table->timestamp('enabled_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id']);
        });

        Schema::create('two_factor_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('code_hash');
            $table->timestamp('expires_at');
            $table->timestamps();
            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('two_factor_codes');
        Schema::dropIfExists('two_factor_secrets');
    }
};
