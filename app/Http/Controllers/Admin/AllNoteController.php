<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Note\UpdateNoteRequest;
use App\Services\NoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AllNoteController extends Controller
{
    public function __construct(
        private readonly NoteService $noteService,
    ) {}

    /**
     * List all notes (or trashed notes) across all users in the admin portal.
     */
    public function index(Request $request): View
    {
        $onlyTrashed = $request->boolean('trashed');
        $notes = $this->noteService->listAllForAdmin(15, $onlyTrashed);

        return view('admin.notes.index', [
            'notes' => $notes,
            'onlyTrashed' => $onlyTrashed,
        ]);
    }

    /**
     * Display a single user note in the admin portal.
     */
    public function show(string $note): View
    {
        $record = $this->noteService->getForAdmin($note);

        return view('admin.notes.show', [
            'note' => $record,
        ]);
    }

    /**
     * Show the admin edit form for any user note.
     */
    public function edit(string $note): View
    {
        $record = $this->noteService->getForAdmin($note);

        return view('admin.notes.edit', [
            'note' => $record,
        ]);
    }

    /**
     * Persist admin updates to any user note.
     */
    public function update(UpdateNoteRequest $request, string $note): RedirectResponse
    {
        $this->noteService->updateForAdmin($note, $request->validated());

        return redirect()
            ->route('admin.notes.show', $note)
            ->with('status', 'Note updated successfully.');
    }

    /**
     * Soft-delete a note and move it to the admin trash.
     */
    public function destroy(string $note): RedirectResponse
    {
        $this->noteService->deleteForAdmin($note);

        return redirect()
            ->route('admin.notes.index')
            ->with('status', 'Note moved to trash.');
    }

    /**
     * Restore a soft-deleted note from the admin trash.
     */
    public function restore(string $note): RedirectResponse
    {
        $this->noteService->restoreForAdmin($note);

        return redirect()
            ->route('admin.notes.index', ['trashed' => 1])
            ->with('status', 'Note restored successfully.');
    }
}
