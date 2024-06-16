<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateQuestionnaireRequest;
use App\Http\Requests\SubmitQuestionnaireRequest;
use App\Http\Resources\QuestionnaireResource;
use App\Repositories\Questionnaire\QuestionnaireRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class QuestionnaireController extends Controller
{
    public function __construct(protected QuestionnaireRepository $questionnaireRepository)
    {
    }

    public function create(CreateQuestionnaireRequest $request): QuestionnaireResource
    {
        $questionnaire = $this->questionnaireRepository->createQuestionnaire($request);
        return new QuestionnaireResource($questionnaire);
    }
    public function listActive(): AnonymousResourceCollection
    {
        $questionnaires = $this->questionnaireRepository->getActiveQuestionnaires();
        return QuestionnaireResource::collection($questionnaires);
    }

    public function sendInvitations(Request $request, $questionnaireId): JsonResponse
    {
        $this->questionnaireRepository->sendInvitations($request, $questionnaireId);
        return response()->json(['message' => 'Invitations sent successfully.']);
    }

    public function accessQuestionnaire($questionnaireId, $accessUrl): QuestionnaireResource
    {
        $questionnaire = $this->questionnaireRepository->accessQuestionnaire($questionnaireId, $accessUrl);
        return new QuestionnaireResource($questionnaire);
    }

    public function submitQuestionnaire(SubmitQuestionnaireRequest $request, $questionnaireId): JsonResponse
    {
        return $this->questionnaireRepository->submitQuestionnaire($request, $questionnaireId);
    }
}
