<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateEmailTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string("name")->nullable();
            $table->string("type");
            $table->text("template");
            $table->boolean("is_active");
            $table->timestamps();
        });
        DB::table('email_templates')->insert(
            array(
                [
                    'type' => 'email_verification_mail',
                    "template"=>'email verification template goes here....',
                    "is_active" => 1
                ],
                [
                    'type' => 'forget_password_mail',
                    "template"=>'forget password email template goes here',
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
        Schema::dropIfExists('email_templates');
    }
}
