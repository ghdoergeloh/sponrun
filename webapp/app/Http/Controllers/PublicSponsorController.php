<?php

namespace App\Http\Controllers;

use App\Models\RunParticipation;
use App\Notifications\NewSponsorNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicSponsorController extends Controller
{
    public function create(RunParticipation $hash): Response|RedirectResponse
    {
        $runpart = $hash;
        abort_if($runpart->sponsoredRun->isElapsed(), 404);

        return Inertia::render('Sponsors/PublicCreate', [
            'runpart' => [
                'hash'    => $runpart->hash,
                'runner'  => $runpart->user->firstname . ' ' . $runpart->user->lastname,
                'project' => $runpart->project?->name,
                'run'     => $runpart->sponsoredRun->name,
            ],
            'newsletterOptional' => (bool) config('app.newsletter_optional', false),
        ]);
    }

    public function store(Request $request, RunParticipation $hash): RedirectResponse
    {
        $runpart = $hash;
        abort_if($runpart->sponsoredRun->isElapsed(), 404);

        $data = $request->validate([
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
        ]);

        $runpart->sponsors()->create(array_merge($data, ['user_id' => $runpart->user_id]));

        $runpart->user->notify(new NewSponsorNotification(
            $data['firstname'] . ' ' . $data['lastname'],
            (float) str_replace(',', '.', $data['donation_per_lap'] ?? '0'),
            (float) str_replace(',', '.', $data['donation_static_max'] ?? '0'),
        ));

        return redirect()
            ->route('run.sponsor.create', $runpart->hash)
            ->with('success', 'Deine Zusage wurde gespeichert. Vielen Dank!');
    }
}
