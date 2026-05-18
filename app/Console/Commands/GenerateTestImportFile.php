<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Faker\Factory as Faker;

class GenerateTestImportFile extends Command
{
    protected $signature = 'test:generate-import {--count=10000 : Number of books to generate}';
    protected $description = 'Generate a large CSV file for testing book imports';

    public function handle()
    {
        $count = (int) $this->option('count');
        $filename = 'test_books_import.csv';
        $path = public_path($filename);
        $faker = Faker::create();

        $this->info("Generating {$count} books in {$filename}...");

        $file = fopen($path, 'w');

        // Header matching Lab 6 requirements
        fputcsv($file, ['ISBN', 'Title', 'Author', 'Price', 'Stock', 'Category', 'Description']);

        $categories = ['Fiction', 'Non-Fiction', 'Science', 'History', 'Technology', 'Biography'];

        for ($i = 0; $i < $count; $i++) {
            fputcsv($file, [
                $this->generateIsbn13($faker),
                $faker->sentence(3),
                $faker->name,
                $faker->randomFloat(2, 5, 500),
                $faker->numberBetween(0, 1000),
                $faker->randomElement($categories),
                $faker->paragraph(2)
            ]);

            if ($i % 1000 === 0 && $i > 0) {
                $this->info("Generated {$i} rows...");
            }
        }

        fclose($file);

        $this->info("File generated successfully at: {$path}");
        $this->info("You can download it at: " . url($filename));
    }

    private function generateIsbn13($faker)
    {
        $prefix = '978';
        $body = str_pad((string)$faker->numberBetween(0, 999999999), 9, '0', STR_PAD_LEFT);
        $digits = $prefix . $body;
        
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$digits[$i] * ($i % 2 === 0 ? 1 : 3);
        }
        
        $checkDigit = (10 - ($sum % 10)) % 10;
        return $digits . $checkDigit;
    }
}
