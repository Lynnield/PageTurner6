<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Composite Index: Category + Publication Year
        if (!$this->indexExists('books', 'books_cat_year_index')) {
            Schema::table('books', function (Blueprint $table) {
                $table->index(['category_id', 'publication_year'], 'books_cat_year_index');
            });
        }
        
        // Covering Index: Price + Stock + ID
        if (!$this->indexExists('books', 'books_price_stock_id_index')) {
            Schema::table('books', function (Blueprint $table) {
                $table->index(['price', 'stock_quantity', 'id'], 'books_price_stock_id_index');
            });
        }
        
        // ISBN Lookup Index (ISBN is unique, but secondary index can help in some cases, 
        // though unique index already exists. Let's keep it if specifically requested by migration)
        if (!$this->indexExists('books', 'books_isbn_index')) {
            Schema::table('books', function (Blueprint $table) {
                $table->index('isbn', 'books_isbn_index');
            });
        }

        // Full-Text Index
        if (!$this->indexExists('books', 'books_fulltext')) {
            DB::statement('ALTER TABLE books ADD FULLTEXT books_fulltext(title, description)');
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $conn = Schema::getConnection()->getSchemaBuilder();
        return collect(DB::select("SHOW INDEX FROM {$table} WHERE Key_name = '{$index}'"))->isNotEmpty();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            if ($this->indexExists('books', 'books_cat_year_index')) {
                $table->dropIndex('books_cat_year_index');
            }
            if ($this->indexExists('books', 'books_price_stock_id_index')) {
                $table->dropIndex('books_price_stock_id_index');
            }
            if ($this->indexExists('books', 'books_isbn_index')) {
                $table->dropIndex('books_isbn_index');
            }
            if ($this->indexExists('books', 'books_fulltext')) {
                $table->dropIndex('books_fulltext');
            }
        });
    }
};
