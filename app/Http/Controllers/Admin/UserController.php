<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Exceptions\BusinessException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Web\StoreWebUserRequest;
use App\Http\Requests\Admin\Web\UpdateWebUserRequest;
use App\Repositories\NoteRepository;
use App\Repositories\PostRepository;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
        private readonly NoteRepository $noteRepository,
        private readonly PostRepository $postRepository,
    ) {}

    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $role = $request->string('role')->toString();
        $onlyTrashed = $request->boolean('trashed');

        $users = $this->userService->listUsersForAdmin(
            $search !== '' ? $search : null,
            $role !== '' ? $role : null,
            15,
            $onlyTrashed
        );

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'role' => $role,
            'onlyTrashed' => $onlyTrashed,
        ]);
    }

    public function restore(string $user): RedirectResponse
    {
        $this->userService->restoreUser($user);

        return redirect()
            ->route('admin.users.index', ['trashed' => 1])
            ->with('status', 'User restored successfully.');
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(StoreWebUserRequest $request): RedirectResponse
    {
        try {
            $user = $this->userService->createUser($request->validated());
        } catch (BusinessException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.users.show', (string) $user->_id)
            ->with('success', 'User created successfully.');
    }

    public function show(string $user): View
    {
        $record = $this->userService->getUserById($user);

        return view('admin.users.show', [
            'user' => $record,
            'notesCount' => $this->noteRepository->countForUser((string) $record->_id),
            'postsCount' => $this->postRepository->countForUser((string) $record->_id),
        ]);
    }

    public function edit(string $user): View
    {
        $record = $this->userService->getUserById($user);

        return view('admin.users.edit', [
            'user' => $record,
        ]);
    }

    public function update(UpdateWebUserRequest $request, string $user): RedirectResponse
    {
        $validated = $request->validated();

        if (($validated['password'] ?? null) === null || $validated['password'] === '') {
            unset($validated['password']);
        }

        try {
            $this->userService->updateUser($user, $validated);
        } catch (BusinessException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    public function destroy(string $user): RedirectResponse
    {
        try {
            $this->userService->deleteUser($user);
        } catch (BusinessException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
