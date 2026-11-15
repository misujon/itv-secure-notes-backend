<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Interest;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class InterestController extends Controller
{
    /**
     * List all active admin-managed interests sorted alphabetically.
     */
    public function index(): JsonResponse
    {
        $interests = Interest::query()
            ->orderBy('name')
            ->get()
            ->map(static fn (Interest $interest): array => [
                'id' => (string) $interest->_id,
                'name' => $interest->name,
            ])
            ->values()
            ->all();

        return ApiResponse::success($interests, 'Interests retrieved successfully.');
    }
}
