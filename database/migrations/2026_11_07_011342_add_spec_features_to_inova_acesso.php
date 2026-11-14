<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabela de disponibilidades de voluntários por evento (Seção 8 do documento oficial)
        if (!Schema::hasTable('event_volunteers')) {
            Schema::create('event_volunteers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
                $table->foreignId('volunteer_id')->constrained('users')->cascadeOnDelete();
                $table->string('status')->default('available'); // available, assigned, withdrawn
                $table->timestamps();

                $table->unique(['event_id', 'volunteer_id']);
            });
        }

        // 2. Novos campos para perfil do voluntário (bloqueios, pendências, redes)
        Schema::table('volunteer_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('volunteer_profiles', 'instagram_url')) {
                $table->string('instagram_url')->nullable()->after('linkedin_url');
            }
            if (!Schema::hasColumn('volunteer_profiles', 'resume_url')) {
                $table->string('resume_url')->nullable()->after('experience');
            }
            if (!Schema::hasColumn('volunteer_profiles', 'pendencies_notes')) {
                $table->text('pendencies_notes')->nullable()->after('completed_services_count');
            }
        });

        // 3. Novos campos para contratações (vinculação de ingressos e confirmação de comparecimento - Seções 10 e 12)
        Schema::table('volunteer_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('volunteer_requests', 'ticket_code')) {
                $table->string('ticket_code')->nullable()->unique()->after('cancellation_reason');
            }
            if (!Schema::hasColumn('volunteer_requests', 'attendance_confirmed')) {
                $table->boolean('attendance_confirmed')->nullable()->after('ticket_code');
            }
            if (!Schema::hasColumn('volunteer_requests', 'attendance_notes')) {
                $table->text('attendance_notes')->nullable()->after('attendance_confirmed');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_volunteers');

        Schema::table('volunteer_profiles', function (Blueprint $table) {
            $table->dropColumn(['instagram_url', 'resume_url', 'pendencies_notes']);
        });

        Schema::table('volunteer_requests', function (Blueprint $table) {
            $table->dropColumn(['ticket_code', 'attendance_confirmed', 'attendance_notes']);
        });
    }
};
