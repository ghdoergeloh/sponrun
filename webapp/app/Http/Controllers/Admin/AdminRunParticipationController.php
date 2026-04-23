<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RunParticipation;
use App\Models\SponsoredRun;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminRunParticipationController extends Controller
{
    public function edit(SponsoredRun $sponrun, RunParticipation $runpart): Response
    {
        $runpart->load(['user', 'project', 'sponsors']);

        return Inertia::render('Admin/RunParticipations/Edit', [
            'sponrun' => $sponrun,
            'runpart' => array_merge($runpart->toArray(), ['share_link' => $runpart->share_link]),
            'projects' => $sponrun->getProjectSelection(),
            'donationSum' => $runpart->calculateDonationSum(),
        ]);
    }

    public function update(Request $request, SponsoredRun $sponrun, RunParticipation $runpart): RedirectResponse
    {
        $data = $request->validate([
            'laps' => 'integer|min:0',
            'project_id' => 'nullable|exists:projects,id',
            'tshirt_size' => 'nullable|in:XS,S,M,L,XL,XXL',
        ]);

        $runpart->update($data);

        return redirect()->route('admin.sponrun.show', $sponrun)->with('success', 'Gespeichert.');
    }

    public function destroy(SponsoredRun $sponrun, RunParticipation $runpart): RedirectResponse
    {
        $runpart->delete();

        return redirect()->route('admin.sponrun.show', $sponrun)->with('success', 'Teilnahme gelöscht.');
    }
}
