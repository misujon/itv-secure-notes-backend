<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Exceptions\BusinessException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Web\UpdateProfileRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    /**
     * Show the signed-in administrator's own profile edit form.
     */
    public function edit(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('admin.profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the signed-in administrator's profile without modifying role.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $this->userService->updateProfile($user, $request->validated());
        } catch (BusinessException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.profile.edit')
            ->with('success', 'Profile updated successfully.');
    }
}
