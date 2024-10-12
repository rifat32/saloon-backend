<?php



use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpertRotasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::create('expert_rotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->date('date');
            $table->json('busy_slots');
            $table->boolean('is_active')->default(false);

            // Ensure business_id is an unsignedBigInteger
            $table->unsignedBigInteger('business_id');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');

            $table->unsignedBigInteger("created_by");
            $table->softDeletes();
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::dropIfExists('expert_rotas');
    }
}



