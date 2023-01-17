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
        Schema::create('charges', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description');
            $table->decimal('total_value');
            $table->integer('number_of_installments');
            $table->string('payment_day');
            /*
            $table->unsignedBigInteger('creditor_id');
            $table->unsignedBigInteger('debtor_id');
            $table->foreign('creditor_id')->references('id')->on('creditors');
            $table->foreign('debtor_id')->references('id')->on('debtors');
            */
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
        /*
        Schema::table('charges', function (Blueprint $table) {
            $table->dropForeign('charges_creditor_id_foreign');
            $table->dropForeign('charges_debtor_id_foreign');
        });
        */
        Schema::dropIfExists('charges');
    }
};
