<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSponsorRequest;
use App\Models\RunParticipation;
use App\Models\Sponsor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
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

    public function store(StoreSponsorRequest $request, RunParticipation $runpart): RedirectResponse
    {
        Gate::authorize('update', $runpart);
        abort_if($runpart->sponsoredRun->isElapsed(), 403, 'Dieser Lauf ist geschlossen.');

        DB::transaction(function () use ($request, $runpart) {
            $runpart->sponsors()->create(array_merge(
                $request->validated(),
                ['user_id' => $runpart->user_id]
            ));
        });

        return redirect()->route('runpart.edit', $runpart)->with('success', 'Sponsor hinzugefügt.');
    }

    public function edit(RunParticipation $runpart, Sponsor $sponsor): Response
    {
        Gate::authorize('view', $runpart);

        return Inertia::render('Sponsors/Edit', ['runpart' => $runpart, 'sponsor' => $sponsor]);
    }

    public function update(StoreSponsorRequest $request, RunParticipation $runpart, Sponsor $sponsor): RedirectResponse
    {
        Gate::authorize('update', $runpart);
        abort_if($runpart->sponsoredRun->isElapsed(), 403, 'Dieser Lauf ist geschlossen.');

        $sponsor->update($request->validated());

        return redirect()->route('runpart.edit', $runpart)->with('success', 'Sponsor gespeichert.');
    }

    public function destroy(RunParticipation $runpart, Sponsor $sponsor): RedirectResponse
    {
        Gate::authorize('update', $runpart);
        $sponsor->delete();

        return redirect()->route('runpart.edit', $runpart)->with('success', 'Sponsor gelöscht.');
    }
}
