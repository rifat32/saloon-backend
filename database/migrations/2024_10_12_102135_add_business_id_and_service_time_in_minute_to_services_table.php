<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBusinessIdAndServiceTimeInMinuteToServicesTable extends Migration
{
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->unsignedBigInteger('business_id')->nullable()->after('id'); // Add nullable business_id column
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade'); // Foreign key for business_id
            $table->integer('service_time_in_minute')->nullable()->after('description'); // Add nullable service_time_in_minute column
        });
    }

    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['business_id']); // Drop the foreign key constraint
            $table->dropColumn('business_id'); // Remove business_id column
            $table->dropColumn('service_time_in_minute'); // Remove service_time_in_minute column
        });
    }

}
