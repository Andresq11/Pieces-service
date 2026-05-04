<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pieces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('theoretical_weight', 8, 2);
            $table->decimal('real_weight', 8, 2)->nullable();
            $table->decimal('weight_difference', 8, 2)->nullable();
            $table->enum('status', ['Pendiente', 'Fabricada'])->default('Pendiente');
            $table->timestamp('manufactured_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pieces');
    }
};
