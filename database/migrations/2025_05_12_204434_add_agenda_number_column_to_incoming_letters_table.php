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
        Schema::table('incoming_letters', function (Blueprint $table) {
            $table->string('agenda_number')->unique()->after('letter_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incoming_letters', function (Blueprint $table) {
            $table->dropColumn('agenda_number');
        });
    }
};
