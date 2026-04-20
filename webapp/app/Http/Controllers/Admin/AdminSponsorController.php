<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RunParticipation;
use App\Models\Sponsor;
use App\Models\SponsoredRun;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminSponsorController extends Controller
{
    public function create(SponsoredRun $sponrun, RunParticipation $runpart): Response
    {
        return Inertia::render('Admin/Sponsors/Create', [
            'sponrun' => $sponrun,
            'runpart' => $runpart->load('user'),
        ]);
    }

    public function store(Request $request, SponsoredRun $sponrun, RunParticipation $runpart): RedirectResponse
    {
        $runpart->sponsors()->create(array_merge(
            $this->validated($request),
            ['user_id' => $runpart->user_id]
        ));

        return redirect()->route('admin.sponrun.runpart.edit', [$sponrun, $runpart])->with('success', 'Sponsor hinzugefügt.');
    }

    public function edit(SponsoredRun $sponrun, RunParticipation $runpart, Sponsor $sponsor): Response
    {
        return Inertia::render('Admin/Sponsors/Edit', [
            'sponrun' => $sponrun,
            'runpart' => $runpart->load('user'),
            'sponsor' => $sponsor,
        ]);
    }

    public function update(Request $request, SponsoredRun $sponrun, RunParticipation $runpart, Sponsor $sponsor): RedirectResponse
    {
        $sponsor->update($this->validated($request));

        return redirect()->route('admin.sponrun.runpart.edit', [$sponrun, $runpart])->with('success', 'Gespeichert.');
    }

    public function destroy(SponsoredRun $sponrun, RunParticipation $runpart, Sponsor $sponsor): RedirectResponse
    {
        $sponsor->delete();

        return redirect()->route('admin.sponrun.runpart.edit', [$sponrun, $runpart])->with('success', 'Sponsor gelöscht.');
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
