<?php

namespace App\Http\Controllers;

use App\Services\SensaeApiService;
use Illuminate\Http\JsonResponse;

class AvailabilityController extends Controller
{
    public function next(SensaeApiService $api): JsonResponse
    {
        $data = $api->getNextAvailability();

        return response()->json($data);
    }
}
