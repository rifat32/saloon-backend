<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductVariationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("automobile_make_id");
            $table->foreign('automobile_make_id')->references('id')->on('automobile_makes')->onDelete('restrict');

            $table->unsignedBigInteger("product_id");
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');

            $table->double("price");





            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_variations');
    }
}
