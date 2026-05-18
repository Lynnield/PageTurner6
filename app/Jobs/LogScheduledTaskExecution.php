<?php

namespace App\Jobs;

use App\Models\ScheduledTaskRun;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * LogScheduledTaskExecution: Record scheduler task execution for monitoring
 */
class LogScheduledTaskExecution implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $command,
        private string $status,
        private ?string $output = null,
        private ?string $errorMessage = null
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            ScheduledTaskRun::create([
                'command' => $this->command,
                'status' => $this->status,
                'output' => $this->output,
                'error_message' => $this->errorMessage,
                'executed_at' => now(),
            ]);

            Log::info("Scheduled task logged", [
                'command' => $this->command,
                'status' => $this->status,
            ]);
        } catch (Throwable $e) {
            Log::error("Failed to log scheduled task execution", [
                'command' => $this->command,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
