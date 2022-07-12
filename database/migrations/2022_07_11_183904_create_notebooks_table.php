<?php

/**
 * Create "notebooks" table to store contacts
 */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notebooks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('creator_uuid')->index();
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('phone');
            $table->string('email')->unique();
            $table->date('birthday')->nullable();
            $table->string('picture')->nullable();
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
        Schema::dropIfExists('notebooks');
    }
};
