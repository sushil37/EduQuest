<?php

namespace Database\Seeders;

use App\Models\Questionnaire;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UsersTableSeeder::class,
            QuestionsTableSeeder::class,
            OptionsTableSeeder::class,
            QuestionnaireTable::class,
            QuestionnaireStudentTableSeeder::class,
//            ResponsesTableSeeder::class,
        ]);
    }
}
