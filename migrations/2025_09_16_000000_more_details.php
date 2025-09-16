<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('installed_addons', function (Blueprint $table) {
            $table->string('friendly_name')->after('provider');
            $table->string('friendly_version')->after('friendly_name');
        });
    }

    public function down()
    {
        Schema::table('installed_addons', function (Blueprint $table) {
            $table->dropColumn('friendly_name');
            $table->dropColumn('friendly_version');
        });
    }
};
