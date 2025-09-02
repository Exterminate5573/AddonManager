<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('installed_addons', function (Blueprint $table) {
            $table->string('uuid')->primary();
            $table->string('version');
            $table->string('provider');
            $table->string('file_hash');
            $table->string('file_path');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('installed_addons');
    }
};
