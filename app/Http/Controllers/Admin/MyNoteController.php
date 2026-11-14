<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Note\StoreNoteRequest;
use App\Http\Requests\Note\UpdateNoteRequest;
use App\Models\User;
use App\Services\NoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MyNoteController extends Controller
{
    public function __construct(
        private readonly NoteService $noteService,
    ) {}

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $notes = $this->noteService->listForUser($user, 15);

        return view('admin.my-notes.index', [
            'notes' => $notes,
        ]);
    }

    public function create(): View
    {
        return view('admin.my-notes.create');
    }

    public function store(StoreNoteRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->noteService->createForUser($user, $request->validated());

        return redirect()
            ->route('admin.my-notes.index')
            ->with('success', 'Note created successfully.');
    }

    public function edit(Request $request, string $note): View
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->noteService->getForUser($user, $note);

        Gate::forUser($user)->authorize('update', $record);

        return view('admin.my-notes.edit', [
            'note' => $record,
        ]);
    }

    public function update(UpdateNoteRequest $request, string $note): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->noteService->updateForUser($user, $note, $request->validated());

        return redirect()
            ->route('admin.my-notes.index')
            ->with('success', 'Note updated successfully.');
    }

    public function destroy(Request $request, string $note): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->noteService->deleteForUser($user, $note);

        return redirect()
            ->route('admin.my-notes.index')
            ->with('success', 'Note deleted successfully.');
    }
}
