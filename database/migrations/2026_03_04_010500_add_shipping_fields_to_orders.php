<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_province')->nullable()->after('shipping_address');
            $table->string('shipping_city')->nullable()->after('shipping_province');
            $table->string('shipping_barangay')->nullable()->after('shipping_city');
            $table->string('shipping_postal_code')->nullable()->after('shipping_barangay');
            $table->string('shipping_street')->nullable()->after('shipping_postal_code');
            $table->string('shipping_building_number')->nullable()->after('shipping_street');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_province',
                'shipping_city',
                'shipping_barangay',
                'shipping_postal_code',
                'shipping_street',
                'shipping_building_number',
            ]);
        });
    }
};
