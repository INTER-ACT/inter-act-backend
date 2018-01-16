<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use \App\Amendments\SubAmendment;

class CreateSubAmendmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_amendments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('amendment_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->text('updated_text');
            $table->text('explanation');
            $table->enum('status', [SubAmendment::PENDING_STATUS, SubAmendment::ACCEPTED_STATUS, SubAmendment::REJECTED_STATUS]);    //TODO: document possible values
            $table->integer('amendment_version')->default(1)->unsigned();   //TODO: update according to parent
            $table->timestamp('handled_at')->nullable();
            $table->text('handle_explanation')->nullable();
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
        Schema::dropIfExists('sub_amendments');
    }
}
