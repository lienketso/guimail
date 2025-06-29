<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tax_code')->unique();
            $table->integer('founded_year')->nullable();
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('companies');
    }
}; 