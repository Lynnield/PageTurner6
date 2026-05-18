<?php

namespace App\Console\Commands;

use App\Models\Book;
use Illuminate\Console\Command;

class IndexBooksBatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'books:index-batch {--chunk=500 : The number of books to index at once}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Batch index books for full-text search';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $chunkSize = $this->option('chunk');
        
        $this->info("Starting batch indexing of active books...");
        
        Book::where('is_active', true)
            ->searchable($chunkSize);
            
        $this->info("Successfully indexed all active books.");
    }
}
