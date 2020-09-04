<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIdentitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('identities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');  // the local user identifier
            $table->string('provider');    // the authentication provider name
            $table->string('provider_id'); // the user identifier within the authentication service
            $table->string('token', 255);
            $table->string('refresh_token', 255)->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->boolean('registration')->default(false);
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
        Schema::dropIfExists('identities');
    }
}
