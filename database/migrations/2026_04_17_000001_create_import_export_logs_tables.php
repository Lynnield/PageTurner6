<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('import_type', 50); // e.g. books
            $table->string('original_filename');
            $table->string('file_disk', 50)->default('local');
            $table->string('stored_path');
            $table->string('mode', 20)->default('skip'); // skip|update
            $table->string('status', 30)->default('queued'); // queued|processing|completed|completed_with_errors|failed
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('processed_rows')->default(0);
            $table->unsignedInteger('success_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['import_type', 'status']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('import_log_failures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_log_id')->constrained('import_logs')->cascadeOnDelete();
            $table->unsignedInteger('row_number')->nullable();
            $table->string('attribute')->nullable();
            $table->json('errors')->nullable();
            $table->json('values')->nullable();
            $table->timestamps();

            $table->index(['import_log_id', 'row_number']);
        });

        Schema::create('export_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('export_type', 50); // books|orders|revenue|users|invoice|user_data_json
            $table->string('format', 10); // csv|xlsx|pdf|json
            $table->json('filters')->nullable();
            $table->json('columns')->nullable();
            $table->string('status', 30)->default('queued'); // queued|processing|completed|failed
            $table->unsignedBigInteger('total_rows')->default(0);
            $table->string('file_disk', 50)->default('local');
            $table->string('stored_path')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['export_type', 'status']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('export_logs');
        Schema::dropIfExists('import_log_failures');
        Schema::dropIfExists('import_logs');
    }
};

