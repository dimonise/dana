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
        Schema::create('deliveries_costs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('weight_from')->nullable();
            $table->integer('weight_to')->nullable();
            $table->string('delivery_service', 50)->nullable();
            $table->integer('tax')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deliveries_costs');
    }
};
