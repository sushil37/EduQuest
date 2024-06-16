<?php

namespace Database\Factories;

use App\Models\Option;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class OptionFactory extends Factory
{
    protected $model = Option::class;

    public function definition()
    {
        return [
            'question_id' => Question::factory(),
            'option_text' => $this->faker->sentence,
            'is_correct' => $this->faker->boolean(25), // 25% chance of being the correct answer
        ];
    }
}
