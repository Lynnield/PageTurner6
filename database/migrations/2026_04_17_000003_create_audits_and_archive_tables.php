<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audits', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->nullableMorphs('user');
            $table->string('event');

            $table->morphs('auditable');

            $table->text('old_values')->nullable();
            $table->text('new_values')->nullable();

            $table->string('url')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('http_method', 10)->nullable();
            $table->uuid('request_uuid')->nullable();

            // Enterprise metadata (imports/exports/backups, etc.)
            $table->json('metadata')->nullable();

            // Tamper detection: HMAC over canonical payload + previous checksum.
            $table->char('checksum', 64)->nullable()->index();
            $table->char('previous_checksum', 64)->nullable()->index();

            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['user_id', 'user_type']);
            $table->index(['event', 'created_at']);
            $table->index(['request_uuid']);
        });

        Schema::create('audits_archive', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->nullableMorphs('user');
            $table->string('event');
            $table->morphs('auditable');
            $table->text('old_values')->nullable();
            $table->text('new_values')->nullable();
            $table->string('url')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('http_method', 10)->nullable();
            $table->uuid('request_uuid')->nullable();
            $table->json('metadata')->nullable();
            $table->char('checksum', 64)->nullable()->index();
            $table->char('previous_checksum', 64)->nullable()->index();

            $table->timestamp('archived_at')->useCurrent();
            $table->timestamps();

            $table->index(['event', 'created_at']);
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audits_archive');
        Schema::dropIfExists('audits');
    }
};

