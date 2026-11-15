<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Interest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use MongoDB\BSON\ObjectId;

class ManageInterestController extends Controller
{
    /**
     * List active or trashed admin-managed interests.
     */
    public function index(Request $request): View
    {
        $onlyTrashed = $request->boolean('trashed');

        $query = $onlyTrashed ? Interest::onlyTrashed() : Interest::query();
        $interests = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.interests.manage.index', [
            'interests' => $interests,
            'onlyTrashed' => $onlyTrashed,
        ]);
    }

    /**
     * Show the form to create a new selectable interest.
     */
    public function create(): View
    {
        return view('admin.interests.manage.create');
    }

    /**
     * Create a new normalized interest or restore a matching trashed interest.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
        ]);

        $normalized = Interest::normalizeName($validated['name']);

        if ($normalized === '') {
            return back()->withErrors(['name' => 'Interest name cannot be empty.'])->withInput();
        }

        $existing = Interest::withTrashed()->where('name', $normalized)->first();

        if ($existing !== null) {
            if ($existing->trashed()) {
                $existing->restore();

                return redirect()->route('admin.interests.manage.index')
                    ->with('success', 'Interest restored and activated.');
            }

            return back()->withErrors(['name' => 'This interest already exists.'])->withInput();
        }

        Interest::query()->create(['name' => $normalized]);

        return redirect()->route('admin.interests.manage.index')
            ->with('success', 'Interest created successfully.');
    }

    /**
     * Show the edit form for an interest.
     */
    public function edit(string $interest): View
    {
        $record = Interest::query()->where('_id', new ObjectId($interest))->firstOrFail();

        return view('admin.interests.manage.edit', [
            'interest' => $record,
        ]);
    }

    /**
     * Update an existing interest name after normalizing and checking uniqueness.
     */
    public function update(Request $request, string $interest): RedirectResponse
    {
        $record = Interest::query()->where('_id', new ObjectId($interest))->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
        ]);

        $normalized = Interest::normalizeName($validated['name']);

        if ($normalized === '') {
            return back()->withErrors(['name' => 'Interest name cannot be empty.'])->withInput();
        }

        $duplicate = Interest::withTrashed()
            ->where('name', $normalized)
            ->where('_id', '!=', $record->_id)
            ->first();

        if ($duplicate !== null) {
            return back()->withErrors(['name' => 'Another interest with this name already exists.'])->withInput();
        }

        $record->name = $normalized;
        $record->save();

        return redirect()->route('admin.interests.manage.index')
            ->with('success', 'Interest updated successfully.');
    }

    /**
     * Soft-delete an interest and move it to the trash.
     */
    public function destroy(string $interest): RedirectResponse
    {
        $record = Interest::query()->where('_id', new ObjectId($interest))->firstOrFail();
        $record->delete();

        return redirect()->route('admin.interests.manage.index')
            ->with('success', 'Interest moved to trash.');
    }

    /**
     * Restore a soft-deleted interest from the trash.
     */
    public function restore(string $interest): RedirectResponse
    {
        $record = Interest::onlyTrashed()->where('_id', new ObjectId($interest))->firstOrFail();
        $record->restore();

        return redirect()->route('admin.interests.manage.index', ['trashed' => 1])
            ->with('success', 'Interest restored successfully.');
    }
}
