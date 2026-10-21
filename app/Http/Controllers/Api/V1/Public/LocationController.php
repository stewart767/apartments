<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\V1\ApiController;
use App\Models\Region;
use App\Models\District;
use App\Models\Ward;
use App\Models\PropertyType;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LocationController extends ApiController
{
    public function regions(): JsonResponse
    {
        $regions = Region::withCount('properties')->orderBy('name')->get();
        return $this->successResponse($regions, 'Regions retrieved.');
    }

    public function districts(Request $request): JsonResponse
    {
        $query = District::withCount('properties')->orderBy('name');
        if ($request->filled('region_id')) {
            $query->where('region_id', $request->input('region_id'));
        }
        return $this->successResponse($query->get(), 'Districts retrieved.');
    }

    public function wards(Request $request): JsonResponse
    {
        $query = Ward::withCount('properties')->orderBy('name');
        if ($request->filled('district_id')) {
            $query->where('district_id', $request->input('district_id'));
        }
        return $this->successResponse($query->get(), 'Wards retrieved.');
    }

    public function propertyTypes(): JsonResponse
    {
        $types = PropertyType::withCount('properties')->get();
        return $this->successResponse($types, 'Property types retrieved.');
    }

    public function roomTypes(): JsonResponse
    {
        $types = RoomType::all();
        return $this->successResponse($types, 'Room types retrieved.');
    }
}
