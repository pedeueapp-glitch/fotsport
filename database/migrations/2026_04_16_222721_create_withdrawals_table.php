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
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('request_amount', 10, 2); // Valor total solicitado
            $table->decimal('fee_amount', 10, 2); // 15% taxa da plataforma
            $table->decimal('net_amount', 10, 2); // Valor líquido final
            $table->string('status')->default('pending'); // pending, processed, rejected
            $table->string('mp_transfer_id')->nullable(); 
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
        Schema::dropIfExists('withdrawals');
    }
};
