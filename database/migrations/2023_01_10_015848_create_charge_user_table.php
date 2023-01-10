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
        Schema::create('charge_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('charge_id');
            $table->enum('status', ['Debtor', 'Creditor']);
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('charge_id')->references('id')->on('charges');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('charge_user', function (Blueprint $table) {
            $table->dropForeign('charge_user_user_id_foreign');
            $table->dropForeign('charge_user_charge_id_foreign');
        });
        Schema::dropIfExists('charge_user');
    }
};
