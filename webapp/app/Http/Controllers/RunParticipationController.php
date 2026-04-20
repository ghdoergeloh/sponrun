<?php

namespace App\Http\Controllers;

use App\Models\RunParticipation;
use App\Models\SponsoredRun;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class RunParticipationController extends Controller
{
    public function index(Request $request): Response
    {
        $participations = $request->user()
            ->runParticipations()
            ->with(['sponsoredRun', 'project'])
            ->withCount('sponsors')
            ->latest()
            ->take(20)
            ->get()
            ->map(fn ($rp) => array_merge($rp->toArray(), [
                'donation_sum' => $rp->calculateDonationSum(),
                'share_link'   => $rp->share_link,
            ]));

        return Inertia::render('RunParticipations/Index', [
            'participations' => $participations,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['sponsored_run_id' => 'required|exists:sponsored_runs,id']);

        $run = SponsoredRun::findOrFail($request->sponsored_run_id);

        abort_if($run->isElapsed(), 403, 'Dieser Lauf ist geschlossen.');

        $already = RunParticipation::where('user_id', $request->user()->id)
            ->where('sponsored_run_id', $run->id)
            ->exists();

        abort_if($already, 422, 'Du nimmst bereits an diesem Lauf teil.');

        $rp = RunParticipation::create([
            'user_id'         => $request->user()->id,
            'sponsored_run_id'=> $run->id,
            'laps'            => 0,
        ]);

        return redirect()->route('runpart.edit', $rp)->with('success', 'Erfolgreich angemeldet.');
    }

    public function show(RunParticipation $runpart): Response
    {
        Gate::authorize('view', $runpart);
        $runpart->load(['sponsoredRun', 'project', 'sponsors']);

        return Inertia::render('RunParticipations/Show', [
            'runpart'    => array_merge($runpart->toArray(), ['share_link' => $runpart->share_link]),
            'donationSum'=> $runpart->calculateDonationSum(),
        ]);
    }

    public function edit(RunParticipation $runpart): Response
    {
        Gate::authorize('view', $runpart);
        $runpart->load(['sponsoredRun', 'project', 'sponsors']);

        return Inertia::render('RunParticipations/Edit', [
            'runpart'    => array_merge($runpart->toArray(), ['share_link' => $runpart->share_link]),
            'projects'   => $runpart->sponsoredRun->getProjectSelection(),
            'donationSum'=> $runpart->calculateDonationSum(),
        ]);
    }

    public function update(Request $request, RunParticipation $runpart): RedirectResponse
    {
        Gate::authorize('update', $runpart);
        abort_if($runpart->sponsoredRun->isElapsed(), 403, 'Dieser Lauf ist geschlossen.');

        $data = $request->validate([
            'laps'       => 'integer|min:0',
            'project_id' => 'nullable|exists:projects,id',
            'tshirt_size'=> 'nullable|in:XS,S,M,L,XL,XXL',
        ]);

        $runpart->update($data);

        return redirect()->route('runpart.edit', $runpart)->with('success', 'Gespeichert.');
    }

    public function destroy(RunParticipation $runpart): RedirectResponse
    {
        Gate::authorize('delete', $runpart);
        $runpart->delete();

        return redirect()->route('dashboard')->with('success', 'Abgemeldet.');
    }
}
