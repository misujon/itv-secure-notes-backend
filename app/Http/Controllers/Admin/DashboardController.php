<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\NoteRepository;
use App\Repositories\PostRepository;
use App\Repositories\UserRepository;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly NoteRepository $noteRepository,
        private readonly PostRepository $postRepository,
    ) {}

    /**
     * Render the admin dashboard with collection counts and recent users/notes.
     */
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'users' => $this->userRepository->countAll(),
                'notes' => $this->noteRepository->countAll(),
                'posts' => $this->postRepository->countAll(),
            ],
            'recentUsers' => $this->userRepository->recent(5),
            'recentNotes' => $this->noteRepository->recent(5),
        ]);
    }
}
