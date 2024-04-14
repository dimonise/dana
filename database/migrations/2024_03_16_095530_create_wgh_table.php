<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wgh', function (Blueprint $table) {
            $table->comment('весо габаритные характеристики');
            $table->integer('id', true);
            $table->integer('category');
            $table->float('weight', 10, 0)->nullable();
            $table->float('width', 10, 0)->nullable();
            $table->float('length', 10, 0)->nullable();
            $table->float('height', 10, 0)->nullable();
            $table->float('repackaging', 10, 0)->nullable();
            $table->float('stacking', 10, 0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wgh');
    }
};
