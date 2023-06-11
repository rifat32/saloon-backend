<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {

            $table->id();
            $table->string("question");
            $table->enum('type', [
             'star',
             'emoji',
             'numbers',
             'heart'
            ])->default("star")->nullable();
            $table->unsignedBigInteger("garage_id")->nullable();
            $table->boolean("is_default")->default(false);
            $table->boolean("is_active")->default(false);

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
        Schema::dropIfExists('questions');
    }
}
