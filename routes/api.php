<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\VolunteerController;
use App\Http\Controllers\Api\VolunteerRequestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Inova Acess — Rotas RESTful da API
|--------------------------------------------------------------------------
*/

// --- Rotas Públicas ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{id}', [EventController::class, 'show']);
Route::get('/events/{id}/accessibility', [EventController::class, 'accessibility']);

Route::get('/volunteers', [VolunteerController::class, 'index']);
Route::get('/volunteers/{id}', [VolunteerController::class, 'show']);
Route::get('/volunteers/{id}/reviews', [ReviewController::class, 'getVolunteerReviews']);

// --- Rotas Protegidas (Autenticação Sanctum) ---
Route::middleware('auth:sanctum')->group(function () {
    // Perfil do Usuário
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    // Eventos (Criação e Gestão por Organizadores ou Admin)
    Route::middleware('role:organizer,admin')->group(function () {
        Route::post('/events', [EventController::class, 'store']);
        Route::put('/events/{id}', [EventController::class, 'update']);
        Route::delete('/events/{id}', [EventController::class, 'destroy']);
    });

    // Disponibilidade de Voluntários por Evento (Seção 8 do documento oficial)
    Route::post('/events/{id}/volunteer-availability', [EventController::class, 'addVolunteerAvailability']);
    Route::delete('/events/{id}/volunteer-availability', [EventController::class, 'removeVolunteerAvailability']);

    // Voluntários (Atualização de Status de Disponibilidade)
    Route::put('/volunteers/status', [VolunteerController::class, 'updateStatus']);

    // Contratações / Solicitações de Acompanhamento (Seções 9, 10 e 12)
    Route::get('/volunteer-requests', [VolunteerRequestController::class, 'index']);
    Route::post('/volunteer-requests', [VolunteerRequestController::class, 'store']);
    Route::get('/volunteer-requests/{id}', [VolunteerRequestController::class, 'show']);
    Route::put('/volunteer-requests/{id}/accept', [VolunteerRequestController::class, 'accept']);
    Route::put('/volunteer-requests/{id}/reject', [VolunteerRequestController::class, 'reject']);
    Route::put('/volunteer-requests/{id}/cancel', [VolunteerRequestController::class, 'cancel']);
    Route::put('/volunteer-requests/{id}/complete', [VolunteerRequestController::class, 'complete']);
    Route::put('/volunteer-requests/{id}/confirm-attendance', [VolunteerRequestController::class, 'confirmAttendance']);

    // Chat entre Cliente e Voluntário (Restrito a participantes da solicitação aceita)
    Route::get('/requests/{id}/messages', [ChatController::class, 'getMessages']);
    Route::post('/requests/{id}/messages', [ChatController::class, 'sendMessage']);

    // Avaliações de Acompanhamentos
    Route::post('/reviews', [ReviewController::class, 'store']);

    // Painel Administrativo
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('/stats', [AdminController::class, 'stats']);
        Route::get('/users', [AdminController::class, 'users']);
        Route::post('/users/organizer', [AdminController::class, 'createOrganizer']);
        Route::put('/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus']);
        Route::put('/users/{id}/role', [AdminController::class, 'changeUserRole']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
        Route::get('/verifications', [AdminController::class, 'verifications']);
        Route::put('/verifications/{id}', [AdminController::class, 'updateVerification']);
        Route::put('/volunteers/{id}/resolve-pendency', [VolunteerRequestController::class, 'resolvePendency']);
    });
});
