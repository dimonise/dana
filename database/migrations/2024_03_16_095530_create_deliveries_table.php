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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 255);
            $table->string('sub_name', 255)->nullable();
            $table->string('sub_sub_name', 255)->nullable();
            $table->text('content');
            $table->integer('sort')->index('sort');
            $table->integer('is_active')->index('is_active');
            $table->string('price', 255);
            $table->integer('is_default')->index('is_default');
            $table->double('view_if_price_from')->index('view_if_price_from');
            $table->double('view_if_price_to')->index('view_if_price_to');
            $table->integer('disable_address_area')->index('disable_address_area');
            $table->string('service_code', 50);
            $table->integer('ln_is_accept')->index('ln_is_accept');
            $table->string('name_ln1', 255);
            $table->text('content_ln1');
            $table->float('deliverycost_from', 10, 0);
            $table->integer('deliverycost_plus');
            $table->integer('id_1c');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deliveries');
    }
};
