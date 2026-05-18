<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MassBookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $totalRecords = 10000;
        $chunkSize = 1000;
        $startTime = microtime(true);

        $this->command->info("Starting mass seeding of {$totalRecords} books...");

        for ($i = 0; $i < ($totalRecords / $chunkSize); $i++) {
            $books = [];
            
            for ($j = 0; $j < $chunkSize; $j++) {
                $books[] = Book::factory()->make()->toArray();
            }

            DB::table('books')->insert($books);

            $this->command->info("Seeded " . (($i + 1) * $chunkSize) . " / {$totalRecords} books...");
            
            // Garbage collection
            unset($books);
            gc_collect_cycles();
        }

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);
        
        $this->command->info("Successfully seeded {$totalRecords} books in {$duration} seconds.");
    }
}
