<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('personal_token_id')->index('log_personal_token_id');
            $table->foreign('personal_token_id')->references('id')->on('personal_tokens');
            $table->string('url');
            $table->string('request_method');
            $table->json('request_body')->nullable();
            $table->json('request_header');
            $table->ipAddress('ip');
            $table->string('response_code');
            $table->json('response_body')->nullable();
            $table->dateTime('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_logs');
    }
}
