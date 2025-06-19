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
        Schema::table('companies', function (Blueprint $table) {
            $table->integer('founded_year')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('ceo_name')->nullable();
            $table->string('logo')->nullable();
            $table->text('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('founded_year');
            $table->dropColumn('phone');
            $table->dropColumn('address');
            $table->dropColumn('ceo_name');
            $table->dropColumn('logo');
            $table->dropColumn('description');
        });
    }
};
