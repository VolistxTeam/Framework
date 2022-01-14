<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonalTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('personal_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->index('user_id');
            $table->string('key', 32)->index('personal_token_key');
            $table->string('secret', 64);
            $table->string('secret_salt', 16);
            $table->integer('max_count')->nullable();
            $table->json('permissions')->default('[]');
            $table->json('whitelist_range')->default('[]');
            $table->dateTime('activated_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('personal_tokens');
    }
}