<?php

use App\Models\Log;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccessKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('access_keys', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('key', 32)->index('key');
            $table->string('secret', 64);
            $table->string('secret_salt', 16);
            $table->json('whitelist_range')->default('[]');
            $table->json('permissions')->default('[]');
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
        Schema::dropIfExists('access_keys');
    }
}
