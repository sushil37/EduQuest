<?php

namespace App\Repositories\Questionnaire;

use AllowDynamicProperties;
use App\Models\Question;
use App\Models\Questionnaire;
use App\Models\Response;
use App\Models\User;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Mail\Mailer;
CONST FRONTEND_API = 'http://localhost:5173';
#[AllowDynamicProperties]
class QuestionnaireRepository extends BaseRepository
{
    protected Mailer $mailer;

    public function __construct(Questionnaire $model, protected Question $question, protected User $user, protected Response  $response, Mailer $mailer)
    {
        parent::__construct($model);
        $this->mailer = $mailer;
    }

    public function createQuestionnaire($request)
    {
        $questionnaire = $this->model->create([
            'title' => $request->title,
            'expiry_date' => $request->expiry_date,
        ]);

        $physicsQuestions = $this->getRandomQuestionIdsBySubject('Physics');
        $chemistryQuestions = $this->getRandomQuestionIdsBySubject('Chemistry');

        $allQuestions = $physicsQuestions->merge($chemistryQuestions);
        $questionnaire->questions()->attach($allQuestions);

        return $questionnaire;
    }

    private function getRandomQuestionIdsBySubject(string $subject)
    {
        return $this->question::where('subject', $subject)->inRandomOrder()->take(5)->pluck('id');
    }

    public function getActiveQuestionnaires()
    {
        return $this->model::where('expiry_date', '>', Carbon::now())->get();
    }

    private function getRandomQuestions()
    {
        $physicsQuestions = $this->question::where('subject', 'Physics')->inRandomOrder()->take(5)->pluck('id');
        $chemistryQuestions = $this->question::where('subject', 'Chemistry')->inRandomOrder()->take(5)->pluck('id');

        return $physicsQuestions->merge($chemistryQuestions);
    }

    private function sendInvitationEmail($recipientEmail, $questionnaire, $fullAccessUrl): void
    {
        $this->mailer->html("<h3>You are invited to complete the questionnaire: {$questionnaire->title}.</h3><p>Use this URL to access it: <a href=\"$fullAccessUrl\">$fullAccessUrl</a></p>", function ($message) use ($recipientEmail) {
            $message->to($recipientEmail)
                ->subject('Questionnaire Invitation');
        });
    }

    public function sendInvitations($requestData, $questionnaireId): bool
    {
        $questionnaire = $this->model::find($questionnaireId);
        if(is_null($questionnaire)){
            return false;
        }
        $students = $this->user::where('type', 'student')->limit(4)->get();
        $allQuestions = $this->getRandomQuestions();

        // Attach questions to the questionnaire
        $questionnaire->questions()->attach($allQuestions);

        foreach ($students as $student) {
            $accessUrl = Str::random(40);
            $questionnaire->students()->attach($student->id, ['access_url' => $accessUrl]);

            $fullAccessUrl = FRONTEND_API.'/questionnaire/access/' . $questionnaire->id . '/' . $accessUrl;
            $this->sendInvitationEmail($student->email, $questionnaire, $fullAccessUrl);
        }
        return true;
    }

    public function accessQuestionnaire($questionnaireId, $accessUrl)
    {
        Log::info('accessQuestionnaire inputs', [
            'questionnaireId' => $questionnaireId,
            'accessUrl' => $accessUrl
        ]);
        $studentCheck = $this->model::whereHas('students', function ($query) use ($questionnaireId, $accessUrl) {
            $query->where('questionnaire_id', $questionnaireId)
                  ->where('access_url', $accessUrl);
        })->exists();

        Log::info('Student check result', ['exists' => $studentCheck]);
        if (!$studentCheck) {
            Log::warning('No matching student found');
            return null;
        }

        $questionnaire = $this->model::whereHas('students', function ($query) use ($questionnaireId, $accessUrl) {
            $query
                ->where('questionnaire_id', $questionnaireId)
                ->where('access_url', $accessUrl);
        })->with('questions.options')
            ->firstOrFail();


        Log::info('Retrieved questionnaire', ['questionnaire' => $questionnaire]);


        return $questionnaire;
    }

    public function submitQuestionnaire($requestData, $questionnaireId): JsonResponse
    {
        try {
            DB::beginTransaction();
            $questionnaire = $this->model->findOrFail($questionnaireId);
            $answersList = [];
            foreach ($requestData['responses'] as $response){
                $answerItem = [
                    'questionnaire_id' => $questionnaire->id,
                    'student_id' => $requestData['student_id'],
                    'question_id' => $response['question_id'],
                    'option_id' => $response['option_id'],
                ];
                $answersList[] = $answerItem;
            }
            $this->response->insert($answersList);
            DB::commit();

            return response()->json(['message' => 'Responses submitted successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to submit responses.', 'error' => $e->getMessage()], 500);
        }
    }
}
