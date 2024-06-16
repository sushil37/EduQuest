<?php

namespace App\Repositories\Questionnaire;

use AllowDynamicProperties;
use App\Models\Question;
use App\Models\Questionnaire;
use App\Models\Response;
use App\Models\User;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

#[AllowDynamicProperties]
class QuestionnaireRepository extends BaseRepository
{

    public function __construct(Questionnaire $model, Question $question, User $user)
    {
        parent::__construct($model);
        $this->question = $question;
        $this->user = $user;
    }

    public function createQuestionnaire($request)
    {
        $questionnaire = $this->model::create([
            'title' => $request->title,
            'expiry_date' => $request->expiry_date,
        ]);

        $physicsQuestions = $this->question::where('subject', 'physics')->inRandomOrder()->take(5)->pluck('id');
        $chemistryQuestions = $this->question::where('subject', 'chemistry')->inRandomOrder()->take(5)->pluck('id');

        $allQuestions = $physicsQuestions->merge($chemistryQuestions);

        Log::info('Physics Questions', ['questions' => $physicsQuestions]);
        Log::info('Chemistry Questions', ['questions' => $chemistryQuestions]);
        Log::info('All Questions', ['questions' => $allQuestions]);

        $questionnaire->questions()->attach($allQuestions);

        return $questionnaire;
    }

    public function getActiveQuestionnaires()
    {
        return $this->model::where('expiry_date', '>', Carbon::now())->get();
    }

    public function sendInvitations($requestData, $questionnaireId)
    {
        $questionnaire = $this->model::findOrFail($questionnaireId);
        // Fetch students (for testing limit to 4)
        $students = $this->user::where('type', 'student')->limit(4)->get();

        // Fetch questions for this questionnaire
        $physicsQuestions = $this->question::where('subject', 'Physics')->inRandomOrder()->take(5)->pluck('id');
        $chemistryQuestions = $this->question::where('subject', 'Chemistry')->inRandomOrder()->take(5)->pluck('id');

        // Merge physics and chemistry questions
        $allQuestions = $physicsQuestions->merge($chemistryQuestions);

        // Attach questions to the questionnaire
        $questionnaire->questions()->attach($allQuestions);

        // Send invitations to students
        foreach ($students as $student) {
            $accessUrl = Str::random(40);

            // Attach student with access URL to questionnaire
            $questionnaire->students()->attach($student->id, ['access_url' => $accessUrl]);

            $fullAccessUrl = route('questionnaire.access', [
                'questionnaire' => $questionnaire->id,
                'access_url' => $accessUrl,
            ]);

            // Send invitation email
            Mail::raw("You are invited to complete the questionnaire: {$questionnaire->title}. Use this URL to access it: $fullAccessUrl", function ($message) use ($student) {
                $message->to($student->email)
                    ->subject('Questionnaire Invitation');
            });
        }
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
            return null; // Or throw an exception
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

    public function submitQuestionnaire($requestData, $questionnaireId): \Illuminate\Http\JsonResponse
    {
        $validatedData = $requestData->validated();

        try {
            // Find the questionnaire by ID
            $questionnaire = Questionnaire::findOrFail($questionnaireId);

            $answersList = [];
            // Save each response
            foreach ($validatedData['responses'] as $response){
                $answerItem = [
                    'questionnaire_id' => $questionnaire->id,
                    'student_id' => $requestData['student_id'],
                    'question_id' => $response['question_id'],
                    'option_id' => $response['option_id'],
                ];
                $answersList[] = $answerItem;
            }
            Response::insert($answersList);
            DB::commit();

            return response()->json(['message' => 'Responses submitted successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to submit responses.', 'error' => $e->getMessage()], 500);
        }


    }
}
