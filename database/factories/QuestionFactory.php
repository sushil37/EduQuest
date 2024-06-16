<?php
namespace Database\Factories;

use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition()
    {
        return [
            'subject' => $this->faker->randomElement(['Physics', 'Chemistry']),
            'question_text' => $this->faker->name,
        ];
    }
}
