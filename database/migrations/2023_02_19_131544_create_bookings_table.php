<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger("pre_booking_id")->nullable();
            $table->foreign('pre_booking_id')->references('id')->on('pre_bookings')->onDelete('cascade');


            $table->unsignedBigInteger("garage_id");
            $table->foreign('garage_id')->references('id')->on('garages')->onDelete('cascade');
            $table->unsignedBigInteger("customer_id");
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');


            $table->unsignedBigInteger("automobile_make_id");
            $table->foreign('automobile_make_id')->references('id')->on('automobile_makes')->onDelete('restrict');
            $table->unsignedBigInteger("automobile_model_id");
            $table->foreign('automobile_model_id')->references('id')->on('automobile_models')->onDelete('restrict');



            $table->string("car_registration_no");
            $table->date("car_registration_year")->nullable();

            $table->string("additional_information")->nullable();

            $table->enum("coupon_discount_type",['fixed', 'percentage'])->default("fixed")->nullable();
            $table->double("coupon_discount_amount")->nullable()->default(0);

            $table->double("price")->default(0);

            $table->string("coupon_code")->nullable();


            $table->string("fuel")->nullable();
            $table->string("transmission")->nullable();

            $table->date("job_start_date")->nullable();

            // $table->date("job_end_date")->nullable();

            $table->time("job_start_time")->nullable();
            $table->time("job_end_time")->nullable();

            $table->enum("status",["pending","confirmed","rejected_by_client","rejected_by_garage_owner","converted_to_job"]);
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
        Schema::dropIfExists('bookings');
    }
}
