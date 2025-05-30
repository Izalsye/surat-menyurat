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
        Schema::create('incoming_letters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('letter_number')->nullable()->index();
            $table->date('letter_date')->nullable();
            $table->string('sender')->nullable();
            $table->string('subject')->nullable();
            $table->longText('body')->nullable();
            $table->longText('summary')->nullable();
            // classification
            $table->boolean('is_draft')->default(true);
            $table->string('file')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incoming_letters');
    }
};
