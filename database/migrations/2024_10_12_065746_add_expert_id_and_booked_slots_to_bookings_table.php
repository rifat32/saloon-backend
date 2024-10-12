<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExpertIdAndBookedSlotsToBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Add the expert_id foreign key referencing users table
            $table->unsignedBigInteger('expert_id')->nullable()->after('id');
            $table->foreign('expert_id')->references('id')->on('users')->onDelete('cascade');

            // Add the booked_slots JSON column
            $table->json('booked_slots')->nullable()->after('expert_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Drop the foreign key and column
            $table->dropForeign(['expert_id']);
            $table->dropColumn('expert_id');

            // Drop the booked_slots column
            $table->dropColumn('booked_slots');
        });
    }
}
