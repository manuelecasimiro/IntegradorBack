<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('volunteer_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('volunteer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->string('meeting_point')->nullable();
            $table->time('meeting_time')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'cancelled', 'completed'])->default('pending');
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('volunteer_requests');
    }
};
