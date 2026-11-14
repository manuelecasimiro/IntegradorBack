<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVolunteerRequestRequest;
use App\Models\User;
use App\Models\VolunteerRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VolunteerRequestController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = VolunteerRequest::with([
            'client:id,name,email,phone,profile_photo',
            'volunteer:id,name,email,phone,profile_photo',
            'volunteer.volunteerProfile',
            'event',
            'review',
        ]);

        if ($user->isClient()) {
            $query->where('client_id', $user->id);
        } elseif ($user->isVolunteer()) {
            $query->where('volunteer_id', $user->id);
        } elseif (!$user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('client_id', $user->id)
                  ->orWhere('volunteer_id', $user->id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $requests = $query->latest('requested_at')->paginate(15);

        return $this->successResponse($requests, 'Solicitações obtidas com sucesso.');
    }

    public function store(StoreVolunteerRequestRequest $request): JsonResponse
    {
        $client = $request->user();

        $validated = $request->validated();

        if ($client->id == $validated['volunteer_id']) {
            return $this->errorResponse('Você não pode solicitar acompanhamento para si mesmo.', null, 422);
        }

        $volunteer = User::where('id', $validated['volunteer_id'])
            ->where('role', 'volunteer')
            ->first();

        if (!$volunteer) {
            return $this->errorResponse('Voluntário informado não é válido.', null, 404);
        }

        // Seção 12: Voluntários bloqueados por pendências não podem aceitar novas contratações
        if ($volunteer->volunteerProfile && $volunteer->volunteerProfile->availability_status === 'blocked') {
            return $this->errorResponse('Este voluntário possui uma pendência cadastral e está temporariamente bloqueado para novas contratações.', null, 422);
        }

        // Check for existing pending request
        $existing = VolunteerRequest::where('client_id', $client->id)
            ->where('volunteer_id', $volunteer->id)
            ->where('event_id', $validated['event_id'])
            ->whereIn('status', ['pending', 'accepted'])
            ->first();

        if ($existing) {
            return $this->errorResponse('Já existe uma solicitação em andamento com este voluntário para este evento.', null, 409);
        }

        $volunteerRequest = VolunteerRequest::create([
            'client_id' => $client->id,
            'volunteer_id' => $volunteer->id,
            'event_id' => $validated['event_id'],
            'message' => $validated['message'] ?? null,
            'meeting_point' => $validated['meeting_point'] ?? null,
            'meeting_time' => $validated['meeting_time'] ?? null,
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        $volunteerRequest->load(['client', 'volunteer', 'event']);

        return $this->successResponse($volunteerRequest, 'Solicitação de acompanhamento enviada com sucesso! Aguarde o retorno do voluntário.', 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $volunteerRequest = VolunteerRequest::with([
            'client:id,name,phone,profile_photo',
            'client.profile',
            'volunteer:id,name,phone,profile_photo',
            'volunteer.volunteerProfile',
            'event.accessibility',
            'review',
        ])->find($id);

        if (!$volunteerRequest) {
            return $this->errorResponse('Solicitação não encontrada.', null, 404);
        }

        if ($volunteerRequest->client_id !== $user->id && $volunteerRequest->volunteer_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Você não possui autorização para ver esta solicitação.', null, 403);
        }

        return $this->successResponse($volunteerRequest, 'Solicitação carregada com sucesso.');
    }

    public function accept(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $volunteerRequest = VolunteerRequest::find($id);

        if (!$volunteerRequest) {
            return $this->errorResponse('Solicitação não encontrada.', null, 404);
        }

        if ($volunteerRequest->volunteer_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Apenas o voluntário destinatário pode aceitar esta solicitação.', null, 403);
        }

        // Seção 12: Voluntário bloqueado não pode aceitar contratações
        if ($user->volunteerProfile && $user->volunteerProfile->availability_status === 'blocked') {
            return $this->errorResponse('Você possui uma pendência cadastral em aberto e está temporariamente bloqueado para aceitar contratações.', null, 403);
        }

        if ($volunteerRequest->status !== 'pending') {
            return $this->errorResponse('Esta solicitação não está mais pendente.', null, 422);
        }

        // Seção 10: Geração do código identificador único para vincular ingressos do cliente e voluntário
        $ticketCode = 'INOVA-TKT-' . strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 4)) . "-REQ{$volunteerRequest->id}";

        $volunteerRequest->update([
            'status' => 'accepted',
            'accepted_at' => now(),
            'ticket_code' => $ticketCode,
        ]);

        return $this->successResponse($volunteerRequest, 'Solicitação aceita! O chat seguro e o código do ingresso vinculado foram gerados com sucesso.');
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $volunteerRequest = VolunteerRequest::find($id);

        if (!$volunteerRequest) {
            return $this->errorResponse('Solicitação não encontrada.', null, 404);
        }

        if ($volunteerRequest->volunteer_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Apenas o voluntário destinatário pode recusar esta solicitação.', null, 403);
        }

        $volunteerRequest->update([
            'status' => 'rejected',
        ]);

        return $this->successResponse($volunteerRequest, 'Solicitação recusada com sucesso.');
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $volunteerRequest = VolunteerRequest::find($id);

        if (!$volunteerRequest) {
            return $this->errorResponse('Solicitação não encontrada.', null, 404);
        }

        if ($volunteerRequest->client_id !== $user->id && $volunteerRequest->volunteer_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Você não tem permissão para cancelar esta solicitação.', null, 403);
        }

        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $volunteerRequest->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->input('reason', 'Cancelado pelo usuário'),
        ]);

        return $this->successResponse($volunteerRequest, 'Solicitação cancelada.');
    }

    public function complete(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $volunteerRequest = VolunteerRequest::with('volunteer.volunteerProfile')->find($id);

        if (!$volunteerRequest) {
            return $this->errorResponse('Solicitação não encontrada.', null, 404);
        }

        if ($volunteerRequest->client_id !== $user->id && $volunteerRequest->volunteer_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Apenas os participantes podem marcar o acompanhamento como concluído.', null, 403);
        }

        if ($volunteerRequest->status !== 'accepted') {
            return $this->errorResponse('Apenas solicitações aceitas podem ser marcadas como concluídas.', null, 422);
        }

        $volunteerRequest->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Increment volunteer completed count
        if ($volunteerRequest->volunteer && $volunteerRequest->volunteer->volunteerProfile) {
            $volunteerRequest->volunteer->volunteerProfile->increment('completed_services_count');
        }

        return $this->successResponse($volunteerRequest, 'Acompanhamento marcado como concluído com sucesso!');
    }

    /**
     * Confirmação de comparecimento pós-evento pelo cliente (Seção 12 do documento oficial)
     * Caso o voluntário não compareça, é bloqueado para novas contratações gerando pendência.
     */
    public function confirmAttendance(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $volunteerRequest = VolunteerRequest::with(['volunteer.volunteerProfile', 'event'])->find($id);

        if (!$volunteerRequest) {
            return $this->errorResponse('Contratação não encontrada.', null, 404);
        }

        if ($volunteerRequest->client_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Apenas o cliente atendido pode confirmar o comparecimento do voluntário.', null, 403);
        }

        $request->validate([
            'attended' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $attended = $request->boolean('attended');
        $notes = $request->input('notes');

        $volunteerRequest->update([
            'attendance_confirmed' => $attended,
            'attendance_notes' => $notes,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $volunteer = $volunteerRequest->volunteer;
        if ($volunteer && $volunteer->volunteerProfile) {
            if ($attended) {
                $volunteer->volunteerProfile->increment('completed_services_count');
            } else {
                // Voluntário não compareceu -> Bloqueado para novas contratações (Seção 12)
                $volunteer->volunteerProfile->update([
                    'availability_status' => 'blocked',
                    'pendencies_notes' => "Ausência reportada pelo cliente no evento: " . ($volunteerRequest->event->name ?? 'Evento') . ". Motivo: " . ($notes ?: 'Não compareceu ao ponto de encontro acordado.'),
                ]);
            }
        }

        $msg = $attended
            ? 'Comparecimento confirmado com sucesso! Agradecemos sua colaboração.'
            : 'Registro de ausência efetuado. O voluntário foi bloqueado para novas contratações conforme as diretrizes da plataforma.';

        return $this->successResponse($volunteerRequest, $msg);
    }

    /**
     * Regularização de pendências e desbloqueio de voluntários (Seção 12)
     */
    public function resolvePendency(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return $this->errorResponse('Apenas administradores podem regularizar pendências cadastrais.', null, 403);
        }

        $volunteer = User::with('volunteerProfile')->findOrFail($id);
        if ($volunteer->volunteerProfile) {
            $volunteer->volunteerProfile->update([
                'availability_status' => 'available',
                'pendencies_notes' => null,
            ]);
        }

        return $this->successResponse($volunteer, 'Pendência regularizada com sucesso! O voluntário voltou ao status Disponível.');
    }
}
