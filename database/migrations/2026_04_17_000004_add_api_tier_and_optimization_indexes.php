<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'api_tier')) {
                $table->string('api_tier', 20)->default('standard')->after('role'); // standard|premium
                $table->index(['api_tier', 'role']);
            }
        });

        Schema::table('books', function (Blueprint $table) {
            // isbn is already unique; ensure additional query indexes.
            $table->index(['category_id', 'title']);
            $table->index(['author']);
            $table->index(['price']);
            $table->index(['stock_quantity']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']); // order_date surrogate
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_user_id_created_at_index');
            $table->dropIndex('orders_status_created_at_index');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropIndex('books_category_id_title_index');
            $table->dropIndex('books_author_index');
            $table->dropIndex('books_price_index');
            $table->dropIndex('books_stock_quantity_index');
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'api_tier')) {
                $table->dropIndex('users_api_tier_role_index');
                $table->dropColumn('api_tier');
            }
        });
    }
};

