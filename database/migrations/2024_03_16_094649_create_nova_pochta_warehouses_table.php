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
        Schema::create('nova_pochta_warehouses', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('type', 10)->nullable();
            $table->integer('type_office')->nullable();
            $table->string('Description', 255)->nullable();
            $table->string('DescriptionRu', 255)->nullable();
            $table->string('CityRef', 100)->nullable();
            $table->string('PlaceMaxWeightAllowed', 50)->nullable();
            $table->string('Ref', 100)->nullable();
            $table->string('SettlementRef', 100)->nullable();
            $table->string('ShortAddress', 100)->nullable();
            $table->string('Number', 100)->nullable();
            $table->string('Longitude', 100)->nullable();
            $table->string('Latitude', 100)->nullable();
            $table->tinyInteger('active')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nova_pochta_warehouses');
    }
};
