<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description');
            $table->string('location');
            $table->string('address');
            $table->string('city');
            $table->string('state', 2);
            $table->date('event_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('ticket_information')->nullable();
            $table->string('banner_url')->nullable();
            $table->enum('status', ['draft', 'published', 'cancelled', 'completed'])->default('published');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
