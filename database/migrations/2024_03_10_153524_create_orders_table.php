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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id');
            $table->integer('details_id');
            $table->integer('details_count');
            $table->integer('price_purchase');
            $table->integer('price');
            $table->integer('office_id');
            $table->integer('status');
            $table->string('name_client');
            $table->string('sname_client');
            $table->string('phone');
            $table->integer('delivery_type');
            $table->string('delivery_address',300);
            $table->double('delivery_cost');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
