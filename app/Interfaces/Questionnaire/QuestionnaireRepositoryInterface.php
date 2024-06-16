<?php

namespace App\Repositories\Questionnaire;

interface QuestionnaireRepositoryInterface
{
    public function createQuestionnaire($request);

    public function getActiveQuestionnaires();

    public function sendInvitations($requestData, $questionnaireId);

    public function accessQuestionnaire($questionnaireId, $accessUrl);

    public function submitQuestionnaire($requestData, $questionnaireId);
}
