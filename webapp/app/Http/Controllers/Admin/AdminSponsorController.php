<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSponsorRequest;
use App\Models\RunParticipation;
use App\Models\Sponsor;
use App\Models\SponsoredRun;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
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

    public function store(StoreSponsorRequest $request, SponsoredRun $sponrun, RunParticipation $runpart): RedirectResponse
    {
        DB::transaction(function () use ($request, $runpart) {
            $runpart->sponsors()->create(array_merge(
                $request->validated(),
                ['user_id' => $runpart->user_id]
            ));
        });

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

    public function update(StoreSponsorRequest $request, SponsoredRun $sponrun, RunParticipation $runpart, Sponsor $sponsor): RedirectResponse
    {
        $sponsor->update($request->validated());

        return redirect()->route('admin.sponrun.runpart.edit', [$sponrun, $runpart])->with('success', 'Gespeichert.');
    }

    public function destroy(SponsoredRun $sponrun, RunParticipation $runpart, Sponsor $sponsor): RedirectResponse
    {
        $sponsor->delete();

        return redirect()->route('admin.sponrun.runpart.edit', [$sponrun, $runpart])->with('success', 'Sponsor gelöscht.');
    }
}
