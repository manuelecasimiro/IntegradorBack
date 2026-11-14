<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VolunteerProfile;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VolunteerController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request): JsonResponse
    {
        $query = User::select(['id', 'name', 'profile_photo', 'created_at', 'role'])
            ->where('role', 'volunteer')
            ->where('is_active', true)
            ->with(['volunteerProfile']);

        // Search by name
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by availability
        if ($request->filled('status')) {
            $status = $request->input('status');
            $query->whereHas('volunteerProfile', function ($q) use ($status) {
                $q->where('availability_status', $status);
            });
        }

        // Filter by Skill
        if ($request->filled('skill')) {
            $skill = $request->input('skill');
            $query->whereHas('volunteerProfile', function ($q) use ($skill) {
                $q->where('skills', 'like', "%{$skill}%");
            });
        }

        $volunteers = $query->paginate(12);

        return $this->successResponse($volunteers, 'Voluntários listados com sucesso.');
    }

    public function show(int $id): JsonResponse
    {
        $volunteer = User::select(['id', 'name', 'profile_photo', 'created_at', 'role'])
            ->where('role', 'volunteer')
            ->where('is_active', true)
            ->with([
                'volunteerProfile',
                'reviewsReceived' => function ($q) {
                    $q->with('client:id,name,profile_photo')->latest()->take(10);
                }
            ])
            ->find($id);

        if (!$volunteer) {
            return $this->errorResponse('Voluntário não encontrado.', null, 404);
        }

        return $this->successResponse($volunteer, 'Perfil do voluntário carregado com sucesso.');
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isVolunteer()) {
            return $this->errorResponse('Apenas voluntários podem alterar seu status de disponibilidade.', null, 403);
        }

        $validated = $request->validate([
            'availability_status' => ['required', 'in:available,busy,unavailable'],
        ]);

        $profile = $user->volunteerProfile;
        if (!$profile) {
            $profile = VolunteerProfile::create([
                'user_id' => $user->id,
                'availability_status' => $validated['availability_status'],
            ]);
        } else {
            $profile->update([
                'availability_status' => $validated['availability_status'],
            ]);
        }

        return $this->successResponse($profile, 'Status de disponibilidade atualizado com sucesso.');
    }
}
