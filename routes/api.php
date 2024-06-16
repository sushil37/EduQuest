<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuestionnaireController;

Route::post('questionnaires', [QuestionnaireController::class, 'create']);
Route::get('questionnaires/active', [QuestionnaireController::class, 'listActive']);
Route::post('questionnaires/{questionnaire}/send-invitations', [QuestionnaireController::class, 'sendInvitations']);
Route::get('questionnaires/{questionnaire}/access/{access_url}', [QuestionnaireController::class, 'accessQuestionnaire'])
    ->name('questionnaire.access');
Route::post('questionnaires/{questionnaire}/submit', [QuestionnaireController::class, 'submitQuestionnaire']);
