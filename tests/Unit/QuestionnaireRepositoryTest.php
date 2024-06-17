<?php

use App\Models\Option;
use App\Models\Question;
use App\Models\Questionnaire;
use App\Models\Response;
use App\Models\User;
use App\Repositories\Questionnaire\QuestionnaireRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailer;
use Tests\TestCase;

class QuestionnaireRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected QuestionnaireRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new QuestionnaireRepository(
            new Questionnaire(),
            new Question(),
            new User(),
            new Response(),
            $this->createMock(Mailer::class)
        );
    }

    /** @test */
    public function it_creates_questionnaire()
    {
        $requestMock = (object) [
            'title' => 'Sample Questionnaire',
            'expiry_date' => now()->addDays(7),
        ];

        $questionnaire = $this->repository->createQuestionnaire($requestMock);

        $this->assertInstanceOf(Questionnaire::class, $questionnaire);
        $this->assertEquals('Sample Questionnaire', $questionnaire->title);
    }


    /** @test */
    public function it_returns_empty_collection_when_no_active_questionnaires_exist()
    {
        Questionnaire::factory()->count(2)->create([
            'expiry_date' => now()->subDays(1),
        ]);

        $result = $this->repository->getActiveQuestionnaires();

        $this->assertCount(0, $result);
    }

    /** @test */
    public function it_fails_to_send_invitations_with_invalid_questionnaire_id()
    {
        $requestData = (object) [];
        $invalidQuestionnaireId = 999; // Assuming this ID does not exist

        $result = $this->repository->sendInvitations($requestData, $invalidQuestionnaireId);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_fails_to_access_questionnaire_with_invalid_access_url()
    {
        $questionnaireId = 1;
        $invalidAccessUrl = 'invalid_access_token';

        $result = $this->repository->accessQuestionnaire($questionnaireId, $invalidAccessUrl);

        $this->assertNull($result);
    }


    /** @test */
    public function it_returns_active_questionnaires()
    {
        $activeQuestionnaires = Questionnaire::factory()->count(3)->create([
            'expiry_date' => now()->addDays(1),
        ]);

        Questionnaire::factory()->count(2)->create([
            'expiry_date' => now()->subDays(1),
        ]);

        $result = $this->repository->getActiveQuestionnaires();

        $this->assertCount(3, $result);
        $this->assertTrue($result->contains($activeQuestionnaires->first()));
    }

    public function it_sends_invitations()
    {
        Mail::fake(); // Fake the mail sending for testing

        // Mock data
        $requestData = (object) [];
        $questionnaireId = 1;

        // Create mock questionnaire and students
        $questionnaire = Questionnaire::factory()->create(['id' => $questionnaireId]);
        $students = User::factory()->count(4)->create(['type' => 'student']);

        // Mock getRandomQuestions method
        $this->repository->shouldReceive('getRandomQuestions')->andReturn(collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]));

        // Call the method being tested
        $result = $this->repository->sendInvitations($requestData, $questionnaireId);

        // Assert that invitations were sent successfully
        $this->assertTrue($result);

        // Assert emails were sent to each student
        Mail::assertSent(function ($mail) use ($students, $questionnaire) {
            return $mail->hasTo($students->pluck('email')->toArray()) &&
                $mail->subject === 'Questionnaire Invitation' &&
                $mail->viewData['questionnaire']->id === $questionnaire->id;
        });

    }

    /** @test */
    public function it_accesses_questionnaire_with_valid_access_url()
    {
        // Mock data
        $questionnaireId = 1;
        $accessUrl = 'valid_access_token';

        $questionnaire = Questionnaire::factory()->create(['id' => $questionnaireId]);
        $student = User::factory()->create(['type' => 'student']);
        $questionnaire->students()->attach($student->id, ['access_url' => $accessUrl]);

        $result = $this->repository->accessQuestionnaire($questionnaireId, $accessUrl);

        $this->assertInstanceOf(Questionnaire::class, $result);
        $this->assertEquals($questionnaireId, $result->id);
    }

    /** @test */
    public function it_does_not_access_questionnaire_with_invalid_access_url()
    {
        // Mock data
        $questionnaireId = 1;
        $accessUrl = 'invalid_access_token';

        // Create mock questionnaire and student with access URL
        $questionnaire = Questionnaire::factory()->create(['id' => $questionnaireId]);
        $student = User::factory()->create(['type' => 'student']);
        $questionnaire->students()->attach($student->id, ['access_url' => 'valid_access_token']);

        $result = $this->repository->accessQuestionnaire($questionnaireId, $accessUrl);

        $this->assertNull($result);
    }

    /** @test */
    public function it_submits_questionnaire_responses_successfully()
    {
        // Create a questionnaire and mock questions
        $questionnaire = Questionnaire::factory()->create();
        $questions = Question::factory()->count(2)->create();
        $options = Option::factory()->count(4)->create();

        // Mock student
        $student = User::factory()->create(['type' => 'student']);

        $requestData = [
            'responses' => [
                ['question_id' => $questions[0]->id, 'option_id' => $options[0]->id],
                ['question_id' => $questions[1]->id, 'option_id' => $options[1]->id],
            ],
            'student_id' => $student->id,
        ];

        $request = new Request($requestData);

        $response = $this->repository->submitQuestionnaire($request, $questionnaire->id);

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertDatabaseCount('responses', count($requestData['responses']));
    }

}
