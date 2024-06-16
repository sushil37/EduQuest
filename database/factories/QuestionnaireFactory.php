<?php

namespace Database\Factories;

use App\Models\Questionnaire;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionnaireFactory extends Factory
{
    protected $model = Questionnaire::class;

    public function definition(): array
    {
        $subjectNames = ['Physics', 'Chemistry'];
        $title = $this->faker->randomElement($subjectNames) . ' Exam';
        $date = $this->faker->dateTimeBetween('+1 day', '+2 years')->format('Y-m-d');
        return [
            'title' => $title. ' on '. $date,
            'expiry_date' => $date
        ];
    }
}
