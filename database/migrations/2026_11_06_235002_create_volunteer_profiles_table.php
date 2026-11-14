<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('volunteer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('biography')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->enum('availability_status', ['available', 'busy', 'unavailable', 'blocked'])->default('available');
            $table->text('experience')->nullable();
            $table->json('skills')->nullable();
            $table->decimal('rating_avg', 3, 2)->default(5.00);
            $table->unsignedInteger('completed_services_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('volunteer_profiles');
    }
};
