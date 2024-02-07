<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_id')->unsigned();
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->bigInteger('mark_id')->unsigned();
            $table->foreign('mark_id')->references('id')->on('marks');
            $table->bigInteger('category_id')->unsigned();
            $table->foreign('category_id')->references('id')->on('categories');
            $table->string('name');
            $table->string('description');
            $table->bigInteger('year');
            $table->bigInteger('kilometres');
            $table->bigInteger('condition');
            $table->bigInteger('fuel');
            $table->decimal('trunk_space', 8, 2)->nullable();
            $table->decimal('tank_space', 8, 2)->nullable();
            $table->bigInteger('weight')->nullable();
            $table->string('image')->nullable();
            $table->bigInteger('buyer_id')->unsigned()->nullable();
            $table->foreign('buyer_id')->references('id')->on('accounts');
            $table->date('buy_date')->nullable();
            $table->SoftDeletes(); 
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
        Schema::dropIfExists('cars');
    }
};
