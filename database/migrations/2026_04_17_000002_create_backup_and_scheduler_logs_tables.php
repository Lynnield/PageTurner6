<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('backup_monitoring', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('default');
            $table->string('status', 30)->default('queued'); // queued|running|success|failure
            $table->string('disk', 50)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->boolean('healthy')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('message')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['status', 'started_at']);
        });

        Schema::create('scheduled_task_runs', function (Blueprint $table) {
            $table->id();
            $table->string('task', 100);
            $table->string('command')->nullable();
            $table->string('status', 20); // success|failure
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->longText('output')->nullable();
            $table->longText('error')->nullable();
            $table->timestamps();

            $table->index(['task', 'started_at']);
            $table->index(['status', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_task_runs');
        Schema::dropIfExists('backup_monitoring');
    }
};

