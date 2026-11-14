<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Models\Review;
use App\Models\VolunteerProfile;
use App\Models\VolunteerRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    use ApiResponseTrait;

    public function store(StoreReviewRequest $request): JsonResponse
    {
        $client = $request->user();
        $validated = $request->validated();

        $volunteerRequest = VolunteerRequest::find($validated['request_id']);

        if (!$volunteerRequest) {
            return $this->errorResponse('Solicitação não encontrada.', null, 404);
        }

        if ($volunteerRequest->client_id !== $client->id) {
            return $this->errorResponse('Apenas o cliente solicitante pode avaliar o voluntário.', null, 403);
        }

        if ($volunteerRequest->status !== 'completed') {
            return $this->errorResponse('Apenas acompanhamentos concluídos podem ser avaliados.', null, 422);
        }

        $existing = Review::where('request_id', $volunteerRequest->id)->first();
        if ($existing) {
            return $this->errorResponse('Esta solicitação já foi avaliada anteriormente.', null, 409);
        }

        return DB::transaction(function () use ($client, $volunteerRequest, $validated) {
            $review = Review::create([
                'request_id' => $volunteerRequest->id,
                'client_id' => $client->id,
                'volunteer_id' => $volunteerRequest->volunteer_id,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]);

            // Recalculate volunteer average rating
            $average = Review::where('volunteer_id', $volunteerRequest->volunteer_id)->avg('rating');

            VolunteerProfile::where('user_id', $volunteerRequest->volunteer_id)
                ->update(['rating_avg' => round($average, 2)]);

            $review->load(['client:id,name,profile_photo']);

            return $this->successResponse($review, 'Avaliação registrada com sucesso! Obrigado pelo feedback.', 201);
        });
    }

    public function getVolunteerReviews(int $volunteerId): JsonResponse
    {
        $reviews = Review::where('volunteer_id', $volunteerId)
            ->with('client:id,name,profile_photo')
            ->latest()
            ->paginate(10);

        return $this->successResponse($reviews, 'Avaliações carregadas com sucesso.');
    }
}
