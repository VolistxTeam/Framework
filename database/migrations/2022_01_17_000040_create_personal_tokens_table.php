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
        Schema::enableForeignKeyConstraints();
        Schema::create('personal_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('subscription_id')->index('personalToken_subscription_id');
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');
            $table->string('key', 32);
            $table->string('secret', 64);
            $table->string('secret_salt', 16);
            $table->integer('ip_rule');
            $table->json('ip_range')->default('[]');
            $table->integer('country_rule');
            $table->json('country_range')->default('[]');
            $table->json('permissions')->default('[]');
            $table->boolean('hidden')->default(false);
            $table->dateTime('activated_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
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
