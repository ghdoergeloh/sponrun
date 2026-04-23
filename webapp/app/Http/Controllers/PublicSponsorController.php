<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSponsorRequest;
use App\Models\RunParticipation;
use App\Notifications\NewSponsorNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PublicSponsorController extends Controller
{
    public function create(RunParticipation $runHash): Response|RedirectResponse
    {
        $runpart = $runHash;
        abort_if($runpart->sponsoredRun->isElapsed(), 404);

        return Inertia::render('Sponsors/PublicCreate', [
            'runpart' => [
                'hash' => $runpart->hash,
                'runner' => $runpart->user->firstname.' '.$runpart->user->lastname,
                'project' => $runpart->project?->name,
                'run' => $runpart->sponsoredRun->name,
            ],
            'newsletterOptional' => (bool) config('app.newsletter_optional', false),
        ]);
    }

    public function store(StoreSponsorRequest $request, RunParticipation $runHash): RedirectResponse
    {
        $runpart = $runHash;
        abort_if($runpart->sponsoredRun->isElapsed(), 404);

        $data = $request->validated();

        DB::transaction(function () use ($runpart, $data) {
            $runpart->sponsors()->create(array_merge($data, ['user_id' => $runpart->user_id]));
        });

        $runpart->user->notify(new NewSponsorNotification(
            $data['firstname'].' '.$data['lastname'],
            (float) str_replace(',', '.', $data['donation_per_lap'] ?? '0'),
            (float) str_replace(',', '.', $data['donation_static_max'] ?? '0'),
        ));

        return redirect()
            ->route('run.sponsor.create', $runpart->hash)
            ->with('success', 'Deine Zusage wurde gespeichert. Vielen Dank!');
    }
}
