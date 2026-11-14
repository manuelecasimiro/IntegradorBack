<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Message;
use App\Models\VolunteerRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    use ApiResponseTrait;

    public function getMessages(Request $request, int $requestId): JsonResponse
    {
        $user = $request->user();

        $volunteerRequest = VolunteerRequest::with(['client:id,name,profile_photo', 'volunteer:id,name,profile_photo', 'event:id,name'])
            ->find($requestId);

        if (!$volunteerRequest) {
            return $this->errorResponse('Solicitação não encontrada.', null, 404);
        }

        if ($volunteerRequest->client_id !== $user->id && $volunteerRequest->volunteer_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Você não tem permissão para acessar o chat desta solicitação.', null, 403);
        }

        if (!in_array($volunteerRequest->status, ['accepted', 'completed'])) {
            return $this->errorResponse('O chat só fica disponível após a solicitação ser aceita.', null, 403);
        }

        // Mark unread messages sent by other participant as read
        Message::where('request_id', $requestId)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = Message::where('request_id', $requestId)
            ->with('sender:id,name,profile_photo,role')
            ->orderBy('created_at', 'asc')
            ->get();

        return $this->successResponse([
            'request' => $volunteerRequest,
            'messages' => $messages,
        ], 'Mensagens carregadas com sucesso.');
    }

    public function sendMessage(StoreMessageRequest $request, int $requestId): JsonResponse
    {
        $user = $request->user();

        $volunteerRequest = VolunteerRequest::find($requestId);

        if (!$volunteerRequest) {
            return $this->errorResponse('Solicitação não encontrada.', null, 404);
        }

        if ($volunteerRequest->client_id !== $user->id && $volunteerRequest->volunteer_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Você não tem permissão para enviar mensagens nesta solicitação.', null, 403);
        }

        if (!in_array($volunteerRequest->status, ['accepted', 'completed'])) {
            return $this->errorResponse('O envio de mensagens é permitido somente para solicitações aceitas.', null, 403);
        }

        $validated = $request->validated();

        $message = Message::create([
            'request_id' => $requestId,
            'sender_id' => $user->id,
            'message' => $validated['message'],
        ]);

        $message->load('sender:id,name,profile_photo,role');

        return $this->successResponse($message, 'Mensagem enviada com sucesso.', 201);
    }
}
