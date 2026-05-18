<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Faker\Factory;

$faker = Factory::create();
$filename = 'books_50k.csv';
$handle = fopen($filename, 'w');

// Headers
fputcsv($handle, ['isbn', 'title', 'author', 'price', 'stock', 'category', 'description']);

$categories = [
    'Fiction', 'Non-Fiction', 'Science', 'Technology', 'History', 
    'Biography', 'Children’s Literature', 'Mystery', 'Romance', 
    'Fantasy', 'Self-Help', 'Business', 'Health', 'Travel', 'Cooking'
];

echo "Generating 50,000 books...\n";

for ($i = 0; $i < 50000; $i++) {
    fputcsv($handle, [
        $faker->isbn13(),
        $faker->sentence(3),
        $faker->name(),
        $faker->randomFloat(2, 5, 500),
        $faker->numberBetween(0, 1000),
        $faker->randomElement($categories),
        $faker->paragraph(2)
    ]);

    if (($i + 1) % 10000 === 0) {
        echo "Generated " . ($i + 1) . " rows...\n";
    }
}

fclose($handle);
echo "Finished! File saved as: {$filename}\n";
