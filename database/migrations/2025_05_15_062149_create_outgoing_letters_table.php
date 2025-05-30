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
        Schema::create('outgoing_letters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('letter_number')->nullable()->index();
            $table->string('agenda_number')->unique();
            $table->date('letter_date')->nullable();
            $table->string('recipient')->nullable();
            $table->string('subject')->nullable();
            $table->longText('body')->nullable();
            $table->longText('summary')->nullable();
            $table->boolean('is_draft')->default(true);
            $table->string('file')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('disposition_id')->nullable()->constrained('dispositions')->nullOnDelete();
            $table->foreignUuid('incoming_letter_id')->nullable()->constrained('incoming_letters')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('outgoing_letter_category', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('outgoing_letter_id')->constrained('outgoing_letters')->cascadeOnDelete();
            $table->foreignId('letter_category_id')->constrained('letter_categories')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outgoing_letter_category');
        Schema::dropIfExists('outgoing_letters');
    }
};
