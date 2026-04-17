<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('photos', function (Blueprint $table) {
            // Fotógrafo que fez o upload desta foto específica
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null')->after('event_id');
        });
    }

    public function down()
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
