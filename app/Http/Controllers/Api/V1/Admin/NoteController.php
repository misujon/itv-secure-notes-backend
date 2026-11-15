<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\NoteResource;
use App\Services\NoteService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function __construct(
        private readonly NoteService $noteService,
    ) {}

    /**
     * Paginate all notes across all users for administrators.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);
        $notes = $this->noteService->listAllForAdmin($perPage);

        return ApiResponse::paginated($notes, NoteResource::class);
    }

    /**
     * Retrieve any user's note by ID for administrators.
     */
    public function show(string $id): JsonResponse
    {
        $note = $this->noteService->getForAdmin($id);

        return ApiResponse::success(
            NoteResource::make($note)->resolve(),
            'Note retrieved successfully.'
        );
    }
}
