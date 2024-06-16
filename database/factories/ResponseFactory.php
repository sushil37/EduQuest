<?php

namespace Database\Factories;

use App\Models\Option;
use App\Models\Question;
use App\Models\Questionnaire;
use App\Models\Response;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResponseFactory extends Factory
{
    protected $model = Response::class;

    public function definition()
    {
        return [
            'questionnaire_id' => Questionnaire::factory(),
            'student_id' => User::factory(),
            'question_id' => Question::factory(),
            'option_id' => Option::factory(),
        ];
    }
}
