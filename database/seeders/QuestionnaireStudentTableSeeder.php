<?php

namespace Database\Seeders;

use App\Models\Questionnaire;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuestionnaireStudentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $questionnaires = Questionnaire::all();
        $students = User::all();

        foreach ($questionnaires as $questionnaire) {
            foreach ($students as $student) {
                DB::table('questionnaire_student')->insert([
                    'questionnaire_id' => $questionnaire->id,
                    'student_id' => $student->id,
                    'access_url' => Str::random(32)
                ]);
            }
        }
    }
}
