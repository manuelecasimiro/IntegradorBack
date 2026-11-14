<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\VolunteerProfile;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Seção 4: Validação do código de autorização exclusivo para cadastro de Administrador
        if ($validated['role'] === 'admin') {
            $requiredCode = env('ADMIN_REGISTRATION_CODE', 'INOVA-ADMIN-2026');
            $providedCode = $request->input('admin_code');

            if (!$providedCode || $providedCode !== $requiredCode) {
                return $this->errorResponse(
                    'Código de autorização de administrador inválido. Apenas administradores credenciados podem concluir este cadastro.',
                    null,
                    403
                );
            }
        }

        return DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'cpf' => $validated['cpf'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'is_active' => true,
            ]);

            if ($user->role === 'volunteer') {
                VolunteerProfile::create([
                    'user_id' => $user->id,
                    'biography' => $validated['experience'] ?? $validated['bio'] ?? null,
                    'linkedin_url' => $validated['linkedin_url'] ?? null,
                    'availability_status' => 'available',
                    'experience' => $validated['experience'] ?? null,
                    'skills' => $validated['skills'] ?? ['Acolhimento', 'Orientação'],
                    'rating_avg' => 5.00,
                    'completed_services_count' => 0,
                ]);
            } else {
                UserProfile::create([
                    'user_id' => $user->id,
                    'bio' => $validated['bio'] ?? null,
                    'accessibility_needs' => $validated['accessibility_needs'] ?? null,
                ]);
            }

            $user->load(['profile', 'volunteerProfile']);
            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse([
                'user' => $user,
                'token' => $token,
            ], 'Cadastro realizado com sucesso! Bem-vindo(a) ao Inova Acess.', 201);
        });
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse('E-mail ou senha incorretos.', null, 401);
        }

        if (!$user->is_active) {
            return $this->errorResponse('Esta conta está inativa. Entre em contato com o suporte.', null, 403);
        }

        $user->load(['profile', 'volunteerProfile']);
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token,
        ], 'Login efetuado com sucesso!');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Sessão encerrada com sucesso.');
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user()->load(['profile', 'volunteerProfile', 'verifications']);

        return $this->successResponse($user, 'Perfil obtido com sucesso.');
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'profile_photo' => ['nullable', 'string'],
            // Client profile
            'bio' => ['nullable', 'string'],
            'accessibility_needs' => ['nullable', 'string'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            // Volunteer profile
            'biography' => ['nullable', 'string'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'availability_status' => ['nullable', 'in:available,busy,unavailable'],
            'experience' => ['nullable', 'string'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string'],
        ]);

        $user->update(array_filter([
            'name' => $validated['name'] ?? $user->name,
            'phone' => $validated['phone'] ?? $user->phone,
            'profile_photo' => $validated['profile_photo'] ?? $user->profile_photo,
        ]));

        if ($user->isVolunteer()) {
            $volunteerProfile = $user->volunteerProfile ?? new VolunteerProfile(['user_id' => $user->id]);
            $volunteerProfile->fill(array_filter([
                'biography' => $validated['biography'] ?? $validated['bio'] ?? $volunteerProfile->biography,
                'linkedin_url' => $validated['linkedin_url'] ?? $volunteerProfile->linkedin_url,
                'availability_status' => $validated['availability_status'] ?? $volunteerProfile->availability_status,
                'experience' => $validated['experience'] ?? $volunteerProfile->experience,
                'skills' => $validated['skills'] ?? $volunteerProfile->skills,
            ]))->save();
        } else {
            $profile = $user->profile ?? new UserProfile(['user_id' => $user->id]);
            $profile->fill(array_filter([
                'bio' => $validated['bio'] ?? $profile->bio,
                'accessibility_needs' => $validated['accessibility_needs'] ?? $profile->accessibility_needs,
                'emergency_contact_name' => $validated['emergency_contact_name'] ?? $profile->emergency_contact_name,
                'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? $profile->emergency_contact_phone,
            ]))->save();
        }

        $user->load(['profile', 'volunteerProfile']);

        return $this->successResponse($user, 'Perfil atualizado com sucesso.');
    }
}
