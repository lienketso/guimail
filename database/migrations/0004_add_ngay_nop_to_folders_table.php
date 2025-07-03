<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->date('ngay_nop')->nullable()->after('sort_order');
        });
    }
    public function down()
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->dropColumn('ngay_nop');
        });
    }
}; 