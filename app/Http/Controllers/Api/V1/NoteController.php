<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Note\StoreNoteRequest;
use App\Http\Requests\Note\UpdateNoteRequest;
use App\Http\Resources\NoteResource;
use App\Models\User;
use App\Services\NoteService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function __construct(
        private readonly NoteService $noteService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);

        $notes = $this->noteService->listForUser($user, $perPage);

        return ApiResponse::paginated($notes, NoteResource::class);
    }

    public function store(StoreNoteRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $note = $this->noteService->createForUser($user, $request->validated());

        return ApiResponse::success(
            NoteResource::make($note)->resolve(),
            'Note created successfully.',
            201
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $note = $this->noteService->getForUser($user, $id);

        return ApiResponse::success(
            NoteResource::make($note)->resolve(),
            'Note retrieved successfully.'
        );
    }

    public function update(UpdateNoteRequest $request, string $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $note = $this->noteService->updateForUser($user, $id, $request->validated());

        return ApiResponse::success(
            NoteResource::make($note)->resolve(),
            'Note updated successfully.'
        );
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->noteService->deleteForUser($user, $id);

        return ApiResponse::success(null, 'Note deleted successfully.');
    }
}
