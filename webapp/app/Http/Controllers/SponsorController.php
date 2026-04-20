<?php

namespace App\Http\Controllers;

use App\Models\RunParticipation;
use App\Models\Sponsor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SponsorController extends Controller
{
    public function create(RunParticipation $runpart): Response
    {
        Gate::authorize('view', $runpart);
        return Inertia::render('Sponsors/Create', ['runpart' => $runpart]);
    }

    public function store(Request $request, RunParticipation $runpart): RedirectResponse
    {
        Gate::authorize('update', $runpart);
        abort_if($runpart->sponsoredRun->isElapsed(), 403, 'Dieser Lauf ist geschlossen.');

        $data = $this->validated($request);
        $runpart->sponsors()->create(array_merge($data, ['user_id' => $runpart->user_id]));

        return redirect()->route('runpart.edit', $runpart)->with('success', 'Sponsor hinzugefügt.');
    }

    public function edit(RunParticipation $runpart, Sponsor $sponsor): Response
    {
        Gate::authorize('view', $runpart);
        return Inertia::render('Sponsors/Edit', ['runpart' => $runpart, 'sponsor' => $sponsor]);
    }

    public function update(Request $request, RunParticipation $runpart, Sponsor $sponsor): RedirectResponse
    {
        Gate::authorize('update', $runpart);
        abort_if($runpart->sponsoredRun->isElapsed(), 403, 'Dieser Lauf ist geschlossen.');

        $sponsor->update($this->validated($request));

        return redirect()->route('runpart.edit', $runpart)->with('success', 'Sponsor gespeichert.');
    }

    public function destroy(RunParticipation $runpart, Sponsor $sponsor): RedirectResponse
    {
        Gate::authorize('update', $runpart);
        $sponsor->delete();

        return redirect()->route('runpart.edit', $runpart)->with('success', 'Sponsor gelöscht.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'firstname'           => 'required|string|max:255',
            'lastname'            => 'required|string|max:255',
            'street'              => 'required|string|max:255',
            'housenumber'         => 'required|string|max:31',
            'postcode'            => 'required|string|size:5',
            'city'                => 'required|string|max:255',
            'phone'               => 'nullable|string|max:255',
            'email'               => 'nullable|email|max:255',
            'donation_per_lap'    => ['nullable', 'required_without:donation_static_max', 'regex:/^\d+[,.]?\d{0,2}$/'],
            'donation_static_max' => ['nullable', 'required_without:donation_per_lap', 'regex:/^\d+[,.]?\d{0,2}$/'],
            'wants_newsletter'    => 'nullable|boolean',
            'ext_personnel_no'    => 'nullable|integer',
        ]);
    }
}
