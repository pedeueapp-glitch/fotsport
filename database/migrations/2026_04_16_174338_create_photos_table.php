<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('original_path'); // Secured path
            $table->string('watermarked_path'); // Public path
            $table->decimal('price', 8, 2)->default(10.00); // Ex: R$ 10.00
            $table->json('face_ids')->nullable(); // Arrays of identified face embeddings or similar logic. Right now we can just store boolean if processed.
            $table->boolean('face_processed')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('photos');
    }
};
