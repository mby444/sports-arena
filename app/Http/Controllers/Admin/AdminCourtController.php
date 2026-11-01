<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCourtRequest;
use App\Http\Requests\Admin\UpdateCourtRequest;
use App\Models\Court;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminCourtController extends Controller
{
    /**
     * Display court management list.
     */
    public function index(): View
    {
        $venues = Venue::active()->get();
        $courts = Court::with('venue')->get();

        return view('admin.courts', [
            'venues' => $venues,
            'courts' => $courts,
        ]);
    }

    /**
     * Create a new court.
     */
    public function store(StoreCourtRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['open_time'] = $validated['open_time'].':00';
        $validated['close_time'] = $validated['close_time'].':00';
        $validated['is_active'] = $request->boolean('is_active', true);

        Court::create($validated);

        return redirect()->route('admin.courts')
            ->with('success', 'Court created successfully.');
    }

    /**
     * Update an existing court.
     */
    public function update(Court $court, UpdateCourtRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['open_time'] = $validated['open_time'].':00';
        $validated['close_time'] = $validated['close_time'].':00';
        $validated['is_active'] = $request->boolean('is_active', true);

        $court->update($validated);

        return redirect()->route('admin.courts')
            ->with('success', "Court '{$court->name}' updated successfully.");
    }

    /**
     * Toggle active/maintenance status of a court.
     */
    public function toggle(Court $court): RedirectResponse
    {
        $court->update([
            'is_active' => ! $court->is_active,
        ]);

        $status = $court->is_active ? 'activated' : 'placed under maintenance';

        return redirect()->route('admin.courts')
            ->with('info', "Court '{$court->name}' is now {$status}.");
    }
}
