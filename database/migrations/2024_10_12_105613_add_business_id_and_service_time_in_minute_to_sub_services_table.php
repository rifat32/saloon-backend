<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBusinessIdAndServiceTimeInMinuteToSubServicesTable extends Migration
{
    public function up()
    {
        Schema::table('sub_services', function (Blueprint $table) {
            $table->unsignedBigInteger('business_id')->nullable()->after('id'); // Add nullable business_id column
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade'); // Foreign key for business_id
        });
    }

    public function down()
    {
        Schema::table('sub_services', function (Blueprint $table) {
            $table->dropForeign(['business_id']); // Drop the foreign key constraint
            $table->dropColumn('business_id'); // Remove business_id column

        });
    }

}
