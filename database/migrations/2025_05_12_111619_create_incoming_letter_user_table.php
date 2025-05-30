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
        Schema::create('incoming_letter_user', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->uuid('incoming_letter_id');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->primary(['incoming_letter_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incoming_letter_user');
    }
};
