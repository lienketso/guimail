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
        Schema::create('product_imports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('excel_file_id')->nullable()->comment('File import');
            $table->unsignedBigInteger('company_id')->nullable()->comment('Công ty');
            $table->string('tax_code')->index()->comment('Mã số thuế');
            $table->string('material_code')->unique()->comment('Mã vật tư tự sinh');
            $table->string('product_name')->comment('Tên sản phẩm');
            $table->string('unit')->nullable()->comment('Đơn vị tính');
            $table->decimal('price', 15, 2)->nullable()->comment('Đơn giá');
            $table->index(['company_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_imports');
    }
};
