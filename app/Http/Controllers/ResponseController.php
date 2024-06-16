<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitResponseRequest;
use App\Repositories\Response\ResponseRepository;
use Illuminate\Http\Request;

class ResponseController extends Controller
{
    public function __construct(protected ResponseRepository $responseRepository)
    {

    }

    public function submitResponse(SubmitResponseRequest $request)
    {
        return $this->responseRepository->submitResponse($request);
    }
}
