<?php

namespace App\Http\Controllers;

use App\Models\SponsoredRun;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        $sponruns = SponsoredRun::where('closed', false)
            ->orderBy('begin')
            ->withCount('participants')
            ->get();

        $userParticipations = $request->user()
            ->runParticipations()
            ->pluck('sponsored_run_id')
            ->all();

        return Inertia::render('Dashboard', [
            'sponruns' => $sponruns,
            'userParticipations' => $userParticipations,
        ]);
    }
}
