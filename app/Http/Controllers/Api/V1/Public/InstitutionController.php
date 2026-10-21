<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\InstitutionResource;
use App\Models\Institution;
use Illuminate\Http\JsonResponse;

class InstitutionController extends ApiController
{
    public function index(): JsonResponse
    {
        $institutions = Institution::withCount('properties')->orderBy('name')->get();
        return $this->successResponse(InstitutionResource::collection($institutions), 'Institutions retrieved.');
    }
}
