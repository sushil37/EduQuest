<?php

namespace Database\Seeders;

use App\Models\Option;
use App\Models\Question;
use Illuminate\Database\Seeder;

class OptionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $question = Question::all();
        foreach ($question as $question) {
            Option::factory()->count(4)->create(['question_id' => $question->id]);
        }
    }
}
