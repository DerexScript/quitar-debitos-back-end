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
        Schema::create('collection_invitations', function (Blueprint $table) {
            $table->id();
            $table->string('invitation_code', 255);
            $table->string('email');
            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('charge_id');
            $table->foreign('charge_id')->references('id')->on('charges');
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
        Schema::table('collection_invitations', function (Blueprint $table) {
            $table->dropForeign('collection_invitations_charge_id_foreign');
        });
        Schema::dropIfExists('collection_invitations');
    }
};
