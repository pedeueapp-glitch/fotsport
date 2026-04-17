<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Client who bought
            $table->foreignId('photo_id')->constrained()->onDelete('cascade');
            $table->string('mp_payment_id')->nullable(); // Mercado Pago payment ID
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->decimal('amount', 8, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchases');
    }
};
