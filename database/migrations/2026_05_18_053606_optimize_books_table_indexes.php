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
        Schema::table('books', function (Blueprint $table) {
            // Composite Index: Category + Published + Status
            $table->index(['category_id', 'published_at', 'is_active'], 'books_cat_pub_active_index');
            
            // Covering Index: Price + Stock + ID (for fast catalog/filtering)
            $table->index(['price', 'stock_quantity', 'id'], 'books_price_stock_id_index');
            
            // ISBN Lookup Index (if not already indexed)
            $table->index('isbn', 'books_isbn_index');
            
            // Active Status Index
            $table->index('is_active', 'books_active_index');

            // Full-Text Index (Title + Description)
            // Note: MySQL supports FULLTEXT on InnoDB tables
            DB::statement('ALTER TABLE books ADD FULLTEXT books_fulltext(title, description)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropIndex('books_cat_pub_active_index');
            $table->dropIndex('books_price_stock_id_index');
            $table->dropIndex('books_isbn_index');
            $table->dropIndex('books_active_index');
            
            $table->dropIndex('books_fulltext');
        });
    }
};
