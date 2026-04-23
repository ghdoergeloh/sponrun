<?php

namespace App\Http\Controllers\Admin;

use App\Exports\RunEvaluationExport;
use App\Http\Controllers\Controller;
use App\Models\Projectlist;
use App\Models\SponsoredRun;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SponsoredRunController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/SponsoredRuns/Index', [
            'sponruns' => SponsoredRun::withCount('participants')->orderBy('begin', 'desc')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/SponsoredRuns/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        SponsoredRun::create($data);

        return redirect()->route('admin.sponrun.index')->with('success', 'Sponsorenlauf erstellt.');
    }

    public function show(SponsoredRun $sponrun): Response
    {
        $sponrun->load(['runParticipations.user', 'runParticipations.project', 'runParticipations.sponsors', 'projectlists']);

        return Inertia::render('Admin/SponsoredRuns/Show', [
            'sponrun' => array_merge($sponrun->toArray(), [
                'total_laps' => $sponrun->totalLaps(),
                'total_donation_sum' => $sponrun->totalDonationSum(),
                'most_laps_runners' => $sponrun->participantionsMostLaps(),
                'most_sponsors_runners' => $sponrun->participantionsMostSponsors(),
                'highest_donation_runners' => $sponrun->participantionsHighestDonation(),
                'oldest_participants' => $sponrun->oldestParticipants(),
                'youngest_participants' => $sponrun->youngestParticipants(),
            ]),
            'runParticipations' => $sponrun->runParticipations->map(fn ($rp) => array_merge(
                $rp->toArray(),
                ['donation_sum' => $rp->calculateDonationSum(), 'share_link' => $rp->share_link]
            )),
        ]);
    }

    public function edit(SponsoredRun $sponrun): Response
    {
        $sponrun->load('projectlists');
        $assignedIds = $sponrun->projectlists->pluck('id');

        return Inertia::render('Admin/SponsoredRuns/Edit', [
            'sponrun' => $sponrun,
            'assignedProjectlists' => $sponrun->projectlists,
            'availableProjectlists' => Projectlist::whereNotIn('id', $assignedIds)->get(),
        ]);
    }

    public function update(Request $request, SponsoredRun $sponrun): RedirectResponse
    {
        $sponrun->update($this->validated($request));

        return redirect()->route('admin.sponrun.edit', $sponrun)->with('success', 'Gespeichert.');
    }

    public function destroy(SponsoredRun $sponrun): RedirectResponse
    {
        $sponrun->delete();

        return redirect()->route('admin.sponrun.index')->with('success', 'Sponsorenlauf gelöscht.');
    }

    public function close(SponsoredRun $sponrun): RedirectResponse
    {
        $sponrun->update(['closed' => true]);

        return redirect()->back()->with('success', 'Lauf gesperrt.');
    }

    public function reopen(SponsoredRun $sponrun): RedirectResponse
    {
        $sponrun->update(['closed' => false]);

        return redirect()->back()->with('success', 'Lauf wieder geöffnet.');
    }

    public function addProjectlists(Request $request, SponsoredRun $sponrun): RedirectResponse
    {
        $request->validate(['projectlist_ids' => 'required|array', 'projectlist_ids.*' => 'exists:projectlists,id']);
        $sponrun->projectlists()->syncWithoutDetaching($request->projectlist_ids);

        return redirect()->route('admin.sponrun.edit', $sponrun)->with('success', 'Projektlisten hinzugefügt.');
    }

    public function removeProjectlists(Request $request, SponsoredRun $sponrun): RedirectResponse
    {
        $request->validate(['projectlist_ids' => 'required|array', 'projectlist_ids.*' => 'exists:projectlists,id']);
        $sponrun->projectlists()->detach($request->projectlist_ids);

        return redirect()->route('admin.sponrun.edit', $sponrun)->with('success', 'Projektlisten entfernt.');
    }

    public function evaluation(SponsoredRun $sponrun): BinaryFileResponse
    {
        return Excel::download(
            new RunEvaluationExport($sponrun),
            'Auswertung '.$sponrun->name.'.xlsx'
        );
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'begin' => 'required|date',
            'end' => 'required|date',
            'with_tshirt' => 'required|boolean',
            'street' => 'nullable|string|max:255',
            'housenumber' => 'nullable|string|max:31',
            'postcode' => 'nullable|string|max:5',
            'city' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);
    }
}
