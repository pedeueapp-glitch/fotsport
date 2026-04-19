<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->longText('face_descriptors')->nullable()->after('price');
            $table->boolean('face_indexed')->default(false)->after('face_descriptors');
        });
    }

    public function down()
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->dropColumn(['face_descriptors', 'face_indexed']);
        });
    }

};
