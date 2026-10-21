<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\AmenityResource;
use App\Models\Amenity;
use Illuminate\Http\JsonResponse;

class AmenityController extends ApiController
{
    public function index(): JsonResponse
    {
        $amenities = Amenity::all();
        return $this->successResponse(AmenityResource::collection($amenities), 'Amenities retrieved.');
    }
}
