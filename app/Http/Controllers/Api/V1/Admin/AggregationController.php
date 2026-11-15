<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\AggregationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AggregationController extends Controller
{
    public function __construct(
        private readonly AggregationService $aggregationService,
    ) {}

    /**
     * Return users grouped by their profile interests via MongoDB aggregation.
     */
    public function interests(Request $request): JsonResponse
    {
        $page = max((int) $request->integer('page', 1), 1);
        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);

        $result = $this->aggregationService->groupUsersByInterests($page, $perPage);

        return response()->json([
            'success' => true,
            'data' => $result['data'],
            'meta' => $result['meta'],
        ], 200);
    }
}
