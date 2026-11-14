<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Models\Event;
use App\Models\EventAccessibility;
use App\Models\EventVolunteer;
use App\Models\VolunteerRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request): JsonResponse
    {
        $query = Event::with(['organizer:id,name,email', 'accessibility'])
            ->where('status', 'published');

        // Search by keyword
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        // Filter by City
        if ($request->filled('city')) {
            $query->where('city', $request->input('city'));
        }

        // Filter by Date
        if ($request->filled('date')) {
            $query->whereDate('event_date', $request->input('date'));
        }

        // Filter by Accessibility criteria
        $accessibilityFilters = [
            'ramps',
            'accessible_restrooms',
            'reserved_spaces',
            'wheelchair_accessible',
            'sign_language_interpreter',
            'audio_description',
            'subtitles',
            'tactile_paving',
            'specialized_assistance',
        ];

        foreach ($accessibilityFilters as $filter) {
            if ($request->boolean($filter)) {
                $query->whereHas('accessibility', function ($q) use ($filter) {
                    $q->where($filter, true);
                });
            }
        }

        $events = $query->orderBy('event_date', 'asc')->paginate(12);

        return $this->successResponse($events, 'Eventos listados com sucesso.');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $event = Event::with([
            'organizer:id,name,email',
            'accessibility',
            'eventVolunteers.volunteer:id,name,email,profile_photo',
            'eventVolunteers.volunteer.volunteerProfile',
            'eventVolunteers.volunteer.reviewsReceived',
        ])->find($id);

        if (!$event) {
            return $this->errorResponse('Evento não encontrado.', null, 404);
        }

        // Informação de disponibilidade do usuário logado (se for voluntário)
        $user = $request->user('sanctum');
        $hasVolunteered = false;
        if ($user && $user->isVolunteer()) {
            $hasVolunteered = EventVolunteer::where('event_id', $id)
                ->where('volunteer_id', $user->id)
                ->where('status', 'available')
                ->exists();
        }

        $eventData = $event->toArray();
        $eventData['has_volunteered'] = $hasVolunteered;

        return $this->successResponse($eventData, 'Detalhes do evento carregados com sucesso.');
    }

    /**
     * Voluntário marca "Tenho disponibilidade" no evento (Seção 8 do documento oficial)
     */
    public function addVolunteerAvailability(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$user->isVolunteer()) {
            return $this->errorResponse('Apenas voluntários podem registrar disponibilidade em eventos.', null, 403);
        }

        $profile = $user->volunteerProfile;
        if ($profile && $profile->availability_status === 'blocked') {
            return $this->errorResponse('Seu cadastro possui pendência por ausência em evento anterior e está temporariamente bloqueado para novas contratações.', null, 403);
        }

        $event = Event::find($id);
        if (!$event) {
            return $this->errorResponse('Evento não encontrado.', null, 404);
        }

        // Impede registros duplicados
        $record = EventVolunteer::updateOrCreate(
            ['event_id' => $event->id, 'volunteer_id' => $user->id],
            ['status' => 'available']
        );

        return $this->successResponse($record, 'Disponibilidade registrada com sucesso para este evento!');
    }

    /**
     * Voluntário retira sua disponibilidade do evento quando não houver contratação ativa
     */
    public function removeVolunteerAvailability(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        // Verifica se há impedimento contratual (contratação aceita ou pendente)
        $hasContract = VolunteerRequest::where('event_id', $id)
            ->where('volunteer_id', $user->id)
            ->whereIn('status', ['pending', 'accepted'])
            ->exists();

        if ($hasContract) {
            return $this->errorResponse('Não é permitido retirar a disponibilidade pois você possui uma contratação ativa ou pendente para este evento.', null, 422);
        }

        EventVolunteer::where('event_id', $id)
            ->where('volunteer_id', $user->id)
            ->delete();

        return $this->successResponse(null, 'Disponibilidade retirada com sucesso.');
    }

    public function store(StoreEventRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        return DB::transaction(function () use ($user, $validated) {
            $event = Event::create([
                'organizer_id' => $user->id,
                'name' => $validated['name'],
                'description' => $validated['description'],
                'location' => $validated['location'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'state' => strtoupper($validated['state']),
                'event_date' => $validated['event_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'ticket_information' => $validated['ticket_information'] ?? null,
                'banner_url' => $validated['banner_url'] ?? null,
                'status' => 'published',
            ]);

            EventAccessibility::create([
                'event_id' => $event->id,
                'ramps' => $validated['ramps'] ?? false,
                'accessible_restrooms' => $validated['accessible_restrooms'] ?? false,
                'reserved_spaces' => $validated['reserved_spaces'] ?? false,
                'wheelchair_accessible' => $validated['wheelchair_accessible'] ?? false,
                'sign_language_interpreter' => $validated['sign_language_interpreter'] ?? false,
                'audio_description' => $validated['audio_description'] ?? false,
                'subtitles' => $validated['subtitles'] ?? false,
                'tactile_paving' => $validated['tactile_paving'] ?? false,
                'specialized_assistance' => $validated['specialized_assistance'] ?? false,
                'additional_notes' => $validated['additional_notes'] ?? null,
            ]);

            $event->load('accessibility');

            return $this->successResponse($event, 'Evento cadastrado com sucesso!', 201);
        });
    }

    public function update(StoreEventRequest $request, int $id): JsonResponse
    {
        $event = Event::find($id);

        if (!$event) {
            return $this->errorResponse('Evento não encontrado.', null, 404);
        }

        $user = $request->user();
        if ($event->organizer_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Você não tem permissão para editar este evento.', null, 403);
        }

        $validated = $request->validated();

        $event->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'location' => $validated['location'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'state' => strtoupper($validated['state']),
            'event_date' => $validated['event_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'ticket_information' => $validated['ticket_information'] ?? $event->ticket_information,
            'banner_url' => $validated['banner_url'] ?? $event->banner_url,
        ]);

        $event->accessibility()->updateOrCreate(
            ['event_id' => $event->id],
            [
                'ramps' => $validated['ramps'] ?? false,
                'accessible_restrooms' => $validated['accessible_restrooms'] ?? false,
                'reserved_spaces' => $validated['reserved_spaces'] ?? false,
                'wheelchair_accessible' => $validated['wheelchair_accessible'] ?? false,
                'sign_language_interpreter' => $validated['sign_language_interpreter'] ?? false,
                'audio_description' => $validated['audio_description'] ?? false,
                'subtitles' => $validated['subtitles'] ?? false,
                'tactile_paving' => $validated['tactile_paving'] ?? false,
                'specialized_assistance' => $validated['specialized_assistance'] ?? false,
                'additional_notes' => $validated['additional_notes'] ?? null,
            ]
        );

        $event->load('accessibility');

        return $this->successResponse($event, 'Evento atualizado com sucesso.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $event = Event::find($id);

        if (!$event) {
            return $this->errorResponse('Evento não encontrado.', null, 404);
        }

        $user = $request->user();
        if ($event->organizer_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Você não tem permissão para remover este evento.', null, 403);
        }

        $event->delete();

        return $this->successResponse(null, 'Evento excluído com sucesso.');
    }

    public function accessibility(int $id): JsonResponse
    {
        $accessibility = EventAccessibility::where('event_id', $id)->first();

        if (!$accessibility) {
            return $this->errorResponse('Recursos de acessibilidade não encontrados para este evento.', null, 404);
        }

        return $this->successResponse($accessibility, 'Recursos de acessibilidade carregados.');
    }
}
