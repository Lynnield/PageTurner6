<?php

use App\Models\Book;
use App\Models\Category;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('translate:content {--from=auto} {--to=en}', function () {
    $from = $this->option('from');
    $to = $this->option('to');
    $endpoint = env('LIBRETRANSLATE_URL', 'https://libretranslate.com/translate');
    $apiKey = env('LIBRETRANSLATE_API_KEY');

    $translate = function ($text) use ($endpoint, $apiKey, $from, $to) {
        if (! $text) {
            return $text;
        }
        try {
            if (class_exists(\Illuminate\Support\Facades\Http::class)) {
                $response = Http::timeout(10)->asJson()->post($endpoint, [
                    'q' => $text,
                    'source' => $from,
                    'target' => $to,
                    'format' => 'text',
                    'api_key' => $apiKey ?: null,
                ]);
                if ($response->ok()) {
                    $data = $response->json();

                    return $data['translatedText'] ?? $text;
                }
            }
        } catch (\Throwable $e) {
        }

        return $text;
    };

    $this->info('Translating categories...');
    Category::chunk(50, function ($chunk) use ($translate) {
        foreach ($chunk as $category) {
            $name = $category->name_en ?: $translate($category->name);
            $desc = $category->description_en ?: $translate($category->description);
            $category->update([
                'name_en' => $name,
                'description_en' => $desc,
            ]);
        }
    });

    $this->info('Translating books...');
    Book::chunk(50, function ($chunk) use ($translate) {
        foreach ($chunk as $book) {
            $title = $book->title_en ?: $translate($book->title);
            $desc = $book->description_en ?: $translate($book->description);
            $book->update([
                'title_en' => $title,
                'description_en' => $desc,
            ]);
        }
    });

    $this->info('Done.');
})->purpose('Translate categories and books into English and store results');
