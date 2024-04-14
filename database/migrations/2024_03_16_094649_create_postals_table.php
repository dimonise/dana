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
        Schema::create('postals', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('city_id')->nullable()->index('city_id');
            $table->string('city_name', 150)->nullable();
            $table->string('city_name_ru', 150)->nullable();
            $table->string('lat', 50)->nullable();
            $table->string('lon', 50)->nullable();
            $table->string('RegionsDescription', 100)->nullable();
            $table->string('RegionsDescriptionRu', 100)->nullable();
            $table->string('AreaDescription', 100)->nullable();
            $table->string('AreaDescriptionRu', 100)->nullable();
            $table->string('np', 100)->nullable()->index('np');
            $table->string('me', 100)->nullable()->index('me');
            $table->string('up', 100)->nullable()->index('up');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('postals');
    }
};
