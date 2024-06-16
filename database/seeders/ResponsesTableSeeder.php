<?php
// database/seeders/ResponsesTableSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Response;
use App\Models\Questionnaire;
use App\Models\User;
use App\Models\Question;
use App\Models\Option;

class ResponsesTableSeeder extends Seeder
{
    public function run()
    {
        $questionnaires = Questionnaire::all();
        $students = User::all();
        $questions = Question::all();

        foreach ($questionnaires as $questionnaire) {
            foreach ($students as $student) {
                foreach ($questions as $question) {
                    Response::create([
                        'questionnaire_id' => $questionnaire->id,
                        'student_id' => $student->id,
                        'question_id' => $question->id,
                        'option_id' => Option::inRandomOrder()->first()->id, // Assuming the option exists
                    ]);
                }
            }
        }
    }
}
