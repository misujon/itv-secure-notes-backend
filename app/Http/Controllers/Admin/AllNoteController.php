<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NoteService;
use Illuminate\View\View;

class AllNoteController extends Controller
{
    public function __construct(
        private readonly NoteService $noteService,
    ) {}

    public function index(): View
    {
        $notes = $this->noteService->listAllForAdmin(15);

        return view('admin.notes.index', [
            'notes' => $notes,
        ]);
    }

    public function show(string $note): View
    {
        $record = $this->noteService->getForAdmin($note);

        return view('admin.notes.show', [
            'note' => $record,
        ]);
    }
}
