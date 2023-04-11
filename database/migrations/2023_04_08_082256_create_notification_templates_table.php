<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateNotificationTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string("name")->nullable();
            $table->string("type");
            $table->text("template");
            $table->text("link");
            $table->boolean("is_active");
            $table->timestamps();
        });

        DB::table('notification_templates')->insert(
            array(
                [

                    'type' => 'bid_created_by_garage_owner',
                    "template"=> json_encode("A garage named [garage_name] posted a bid. its owner is"),
                    "link"=> json_encode("/[garage_id]/[bid_id]"),
                    "is_active" => 1
                ],
                [

                    'type' => 'bid_updated_by_garage_owner',
                    "template"=> json_encode("A garage named [garage_name] updated their bid. its owner is"),
                    "link"=> json_encode("/[garage_id]/[bid_id]"),
                    "is_active" => 1
                ],

                [

                    'type' => 'bid_accepted_by_client',
                    "template"=> json_encode("A client named [customer_name] accepted your bid."),
                    "link"=> json_encode("/[customer_id]/[pre_booking_id]"),
                    "is_active" => 1
                ],

                [

                    'type' => 'bid_rejected_by_client',
                    "template"=> json_encode("A client named [customer_name] rejected your bid."),
                    "link"=> json_encode("/[customer_id]/[pre_booking_id]"),
                    "is_active" => 1
                ],



            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_templates');
    }
}
