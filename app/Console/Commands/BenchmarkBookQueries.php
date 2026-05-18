<?php

namespace App\Console\Commands;

use App\Repositories\BookRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BenchmarkBookQueries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'benchmark:books {--iterations=100 : The number of iterations to run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Benchmark book queries for performance analysis';

    private BookRepository $repository;

    public function __construct(BookRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $iterations = (int) $this->option('iterations');
        $this->info("Running benchmarks with {$iterations} iterations...");

        // Warm up pass
        $this->info("Warming up...");
        $this->repository->getCatalog([], 1);
        
        $results = [];

        // 1. ISBN Lookup
        $isbn = DB::table('books')->where('is_active', true)->value('isbn');
        if ($isbn) {
            $results['ISBN Lookup'] = $this->benchmark(fn() => $this->repository->findByIsbn($isbn), $iterations);
        }

        // 2. Catalog Listing
        $results['Catalog Listing'] = $this->benchmark(fn() => $this->repository->getCatalog([], 20), $iterations);

        // 3. Category Filtering
        $categoryId = DB::table('categories')->value('id');
        if ($categoryId) {
            $results['Category Filtering'] = $this->benchmark(fn() => $this->repository->getCatalog(['category_id' => $categoryId], 20), $iterations);
        }

        // 4. Full-Text Search
        $title = DB::table('books')->where('is_active', true)->value('title');
        if ($title) {
            $searchTerm = explode(' ', $title)[0];
            $results['Full-Text Search'] = $this->benchmark(fn() => \App\Models\Book::search($searchTerm)->take(20)->get(), $iterations);
        }

        $this->displayResults($results);
    }

    private function benchmark(callable $callback, int $iterations): array
    {
        $times = [];
        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);
            $callback();
            $times[] = (microtime(true) - $start) * 1000; // ms
        }

        return [
            'avg' => array_sum($times) / count($times),
            'min' => min($times),
            'max' => max($times),
        ];
    }

    private function displayResults(array $results)
    {
        $headers = ['Query Type', 'Avg (ms)', 'Min (ms)', 'Max (ms)'];
        $rows = [];

        foreach ($results as $type => $stats) {
            $rows[] = [
                $type,
                number_format($stats['avg'], 2),
                number_format($stats['min'], 2),
                number_format($stats['max'], 2),
            ];
        }

        $this->table($headers, $rows);
    }
}
