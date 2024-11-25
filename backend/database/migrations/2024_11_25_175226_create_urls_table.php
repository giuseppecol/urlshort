<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUrlsTable extends Migration
{
    public function up()
    {
        Schema::create('urls', function (Blueprint $table) {
            $table->id();
            $table->string('original_url');
            $table->string('short_code', 8)->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('urls');
    }
}