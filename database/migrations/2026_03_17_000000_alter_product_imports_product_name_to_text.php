<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE product_imports MODIFY product_name TEXT NOT NULL COMMENT "Tên sản phẩm"');
        } else {
            DB::statement('ALTER TABLE product_imports ALTER COLUMN product_name TYPE TEXT');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE product_imports MODIFY product_name VARCHAR(255) NOT NULL COMMENT "Tên sản phẩm"');
        } else {
            DB::statement('ALTER TABLE product_imports ALTER COLUMN product_name TYPE VARCHAR(255)');
        }
    }
};
