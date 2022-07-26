<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKeyphrasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('keyphrases', function (Blueprint $table) {
            $table->id();
            $table->integer('project_id');
            $table->string("keyphrase_id");
            $table->string("keyphrase_name");
            $table->integer('rank')->default(0)->nullable();
            $table->date('created_date');
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
        Schema::dropIfExists('keyphrases');
    }
}
