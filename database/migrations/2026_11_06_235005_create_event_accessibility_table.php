<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_accessibility', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->boolean('ramps')->default(false);
            $table->boolean('accessible_restrooms')->default(false);
            $table->boolean('reserved_spaces')->default(false);
            $table->boolean('wheelchair_accessible')->default(false);
            $table->boolean('sign_language_interpreter')->default(false);
            $table->boolean('audio_description')->default(false);
            $table->boolean('subtitles')->default(false);
            $table->boolean('tactile_paving')->default(false);
            $table->boolean('specialized_assistance')->default(false);
            $table->text('additional_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_accessibility');
    }
};
