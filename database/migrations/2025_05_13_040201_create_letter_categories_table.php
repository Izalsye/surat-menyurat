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
        Schema::create('letter_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('incoming_letter_category', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('incoming_letter_id')->constrained('incoming_letters')->cascadeOnDelete();
            $table->foreignId('letter_category_id')->constrained('letter_categories')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incoming_letter_category');
        Schema::dropIfExists('letter_categories');
    }
};
