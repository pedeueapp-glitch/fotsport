<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('purchases', function (Blueprint $table) {
            // First drop foreign key
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
            
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
        });
    }
};