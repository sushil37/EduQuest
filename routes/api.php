<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuestionnaireController;
use App\Http\Controllers\ResponseController;

Route::post('questionnaires', [QuestionnaireController::class, 'create']);
Route::get('questionnaires/active', [QuestionnaireController::class, 'listActive']);
Route::get('/questionnaires/generate', [QuestionnaireController::class, 'generate']);
Route::post('questionnaires/{questionnaire}/send-invitations', [QuestionnaireController::class, 'sendInvitations']);
Route::get('questionnaires/{questionnaire}/access/{access_url}', [QuestionnaireController::class, 'accessQuestionnaire'])
    ->name('questionnaire.access');
Route::post('questionnaires/{questionnaire}/submit', [QuestionnaireController::class, 'submitQuestionnaire']);
//Route::post('responses', [ResponseController::class, 'submitResponse']);
