<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Review;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserVerification;
use App\Models\VolunteerProfile;
use App\Models\VolunteerRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    use ApiResponseTrait;

    public function stats(): JsonResponse
    {
        $totalUsers = User::count();
        $totalVolunteers = User::where('role', 'volunteer')->count();
        $totalClients = User::where('role', 'client')->count();
        $totalOrganizers = User::where('role', 'organizer')->count();
        $bannedUsers = User::where('is_active', false)->count();
        $totalEvents = Event::count();
        $totalRequests = VolunteerRequest::count();
        $completedRequests = VolunteerRequest::where('status', 'completed')->count();
        $pendingVerifications = UserVerification::where('verification_status', 'pending')->count();
        $averageRating = Review::avg('rating') ?? 5.0;

        return $this->successResponse([
            'total_users' => $totalUsers,
            'total_volunteers' => $totalVolunteers,
            'total_clients' => $totalClients,
            'total_organizers' => $totalOrganizers,
            'banned_users' => $bannedUsers,
            'total_events' => $totalEvents,
            'total_requests' => $totalRequests,
            'completed_requests' => $completedRequests,
            'pending_verifications' => $pendingVerifications,
            'average_rating' => round($averageRating, 1),
        ], 'Métricas do sistema obtidas com sucesso.');
    }

    public function users(Request $request): JsonResponse
    {
        $query = User::with(['profile', 'volunteerProfile']);

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('cpf', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(25);

        return $this->successResponse($users, 'Usuários listados com sucesso.');
    }

    public function toggleUserStatus(Request $request, int $id): JsonResponse
    {
        $currentAdmin = $request->user();
        $user = User::find($id);

        if (!$user) {
            return $this->errorResponse('Usuário não encontrado.', null, 404);
        }

        if ($user->id === $currentAdmin->id) {
            return $this->errorResponse('Você não pode banir ou desativar sua própria conta de administrador.', null, 422);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        if (!$user->is_active) {
            // Revoke all tokens immediately so banned user is kicked out
            $user->tokens()->delete();
        }

        $statusText = $user->is_active ? 'reativado com sucesso' : 'banido / desativado do sistema';

        return $this->successResponse($user, "Usuário {$user->name} {$statusText}.");
    }

    public function changeUserRole(Request $request, int $id): JsonResponse
    {
        $currentAdmin = $request->user();
        $user = User::find($id);

        if (!$user) {
            return $this->errorResponse('Usuário não encontrado.', null, 404);
        }

        if ($user->id === $currentAdmin->id && $request->input('role') !== 'admin') {
            return $this->errorResponse('Você não pode revogar seu próprio papel de administrador.', null, 422);
        }

        $validated = $request->validate([
            'role' => ['required', 'in:client,volunteer,organizer,admin'],
        ]);

        $user->role = $validated['role'];
        $user->save();

        // If changed to organizer, ensure profile exists
        if ($user->role === 'organizer' && !$user->profile) {
            UserProfile::create([
                'user_id' => $user->id,
                'bio' => 'Organizador credenciado pelo Administrador Geral.',
            ]);
        }

        // If changed to volunteer, ensure volunteer profile exists
        if ($user->role === 'volunteer' && !$user->volunteerProfile) {
            VolunteerProfile::create([
                'user_id' => $user->id,
                'biography' => 'Voluntário credenciado pelo Administrador.',
                'availability_status' => 'available',
                'skills' => ['Acolhimento', 'Orientação'],
                'rating_avg' => 5.00,
                'completed_services_count' => 0,
            ]);
        }

        $user->load(['profile', 'volunteerProfile']);

        return $this->successResponse($user, "Papel do usuário atualizado para '{$user->role}' com sucesso.");
    }

    public function createOrganizer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'organizer',
            'phone' => $validated['phone'] ?? null,
            'is_active' => true,
        ]);

        UserProfile::create([
            'user_id' => $user->id,
            'bio' => $validated['bio'] ?? 'Produtora cultural e organizadora credenciada.',
        ]);

        $user->load('profile');

        return $this->successResponse($user, "Organizador '{$user->name}' cadastrado e credenciado com sucesso!", 201);
    }

    public function deleteUser(Request $request, int $id): JsonResponse
    {
        $currentAdmin = $request->user();
        $user = User::find($id);

        if (!$user) {
            return $this->errorResponse('Usuário não encontrado.', null, 404);
        }

        if ($user->id === $currentAdmin->id) {
            return $this->errorResponse('Você não pode excluir sua própria conta de administrador.', null, 422);
        }

        $user->delete();

        return $this->successResponse(null, "Usuário {$user->name} excluído do sistema com sucesso.");
    }

    public function verifications(Request $request): JsonResponse
    {
        $query = UserVerification::with('user:id,name,email,cpf,role');

        if ($request->filled('status')) {
            $query->where('verification_status', $request->input('status'));
        }

        $verifications = $query->latest('submitted_at')->paginate(20);

        return $this->successResponse($verifications, 'Solicitações de verificação listadas.');
    }

    public function updateVerification(Request $request, int $id): JsonResponse
    {
        $admin = $request->user();

        $verification = UserVerification::find($id);

        if (!$verification) {
            return $this->errorResponse('Solicitação de verificação não encontrada.', null, 404);
        }

        $validated = $request->validate([
            'verification_status' => ['required', 'in:approved,rejected,in_review'],
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $verification->update([
            'verification_status' => $validated['verification_status'],
            'review_notes' => $validated['review_notes'] ?? null,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        return $this->successResponse($verification, 'Status de verificação atualizado com sucesso.');
    }
}
