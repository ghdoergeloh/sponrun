<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AdminRunParticipationController;
use App\Http\Controllers\Admin\AdminSponsorController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ProjectlistController;
use App\Http\Controllers\Admin\SponsoredRunController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PublicSponsorController;
use App\Http\Controllers\RunParticipationController;
use App\Http\Controllers\SponsorController;
use App\Models\RunParticipation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

$resolveRunHash = fn ($value) => RunParticipation::where('hash', $value)->firstOrFail();

// ── Public (no auth) ──────────────────────────────────────────────────────────
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return Inertia::render('Welcome');
})->name('welcome');

Route::get('run/{runHash}', [PublicSponsorController::class, 'create'])
    ->middleware('throttle:30,1')
    ->name('run.sponsor.create');
Route::post('run/{runHash}/sponsor', [PublicSponsorController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('run.sponsor.store');

Route::bind('runHash', $resolveRunHash);

// ── Authenticated ─────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

    // Account
    Route::get('account/edit', [AccountController::class, 'edit'])->name('account.edit');
    Route::patch('account', [AccountController::class, 'update'])->name('account.update');

    // Run participations (runner)
    Route::get('runpart', [RunParticipationController::class, 'index'])->name('runpart.index');
    Route::post('runpart', [RunParticipationController::class, 'store'])->name('runpart.store');
    Route::get('runpart/{runpart}', [RunParticipationController::class, 'show'])->name('runpart.show');
    Route::get('runpart/{runpart}/edit', [RunParticipationController::class, 'edit'])->name('runpart.edit');
    Route::patch('runpart/{runpart}', [RunParticipationController::class, 'update'])->name('runpart.update');
    Route::delete('runpart/{runpart}', [RunParticipationController::class, 'destroy'])->name('runpart.destroy');

    // Sponsors (runner-owned)
    Route::get('runpart/{runpart}/sponsor/create', [SponsorController::class, 'create'])->name('runpart.sponsor.create');
    Route::post('runpart/{runpart}/sponsor', [SponsorController::class, 'store'])->name('runpart.sponsor.store');
    Route::get('runpart/{runpart}/sponsor/{sponsor}/edit', [SponsorController::class, 'edit'])->name('runpart.sponsor.edit');
    Route::patch('runpart/{runpart}/sponsor/{sponsor}', [SponsorController::class, 'update'])->name('runpart.sponsor.update');
    Route::delete('runpart/{runpart}/sponsor/{sponsor}', [SponsorController::class, 'destroy'])->name('runpart.sponsor.destroy');
});

// ── Admin ─────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Sponsored runs
        Route::get('sponrun', [SponsoredRunController::class, 'index'])->name('sponrun.index');
        Route::get('sponrun/create', [SponsoredRunController::class, 'create'])->name('sponrun.create');
        Route::post('sponrun', [SponsoredRunController::class, 'store'])->name('sponrun.store');
        Route::get('sponrun/{sponrun}', [SponsoredRunController::class, 'show'])->name('sponrun.show');
        Route::get('sponrun/{sponrun}/edit', [SponsoredRunController::class, 'edit'])->name('sponrun.edit');
        Route::patch('sponrun/{sponrun}', [SponsoredRunController::class, 'update'])->name('sponrun.update');
        Route::delete('sponrun/{sponrun}', [SponsoredRunController::class, 'destroy'])->name('sponrun.destroy');
        Route::post('sponrun/{sponrun}/close', [SponsoredRunController::class, 'close'])->name('sponrun.close');
        Route::post('sponrun/{sponrun}/reopen', [SponsoredRunController::class, 'reopen'])->name('sponrun.reopen');
        Route::patch('sponrun/{sponrun}/projectlists/add', [SponsoredRunController::class, 'addProjectlists'])->name('sponrun.addProjectlists');
        Route::patch('sponrun/{sponrun}/projectlists/remove', [SponsoredRunController::class, 'removeProjectlists'])->name('sponrun.removeProjectlists');
        Route::get('sponrun/{sponrun}/evaluation', [SponsoredRunController::class, 'evaluation'])->name('sponrun.evaluation');

        // Admin participations
        Route::get('sponrun/{sponrun}/runpart/{runpart}/edit', [AdminRunParticipationController::class, 'edit'])->name('sponrun.runpart.edit');
        Route::patch('sponrun/{sponrun}/runpart/{runpart}', [AdminRunParticipationController::class, 'update'])->name('sponrun.runpart.update');
        Route::delete('sponrun/{sponrun}/runpart/{runpart}', [AdminRunParticipationController::class, 'destroy'])->name('sponrun.runpart.destroy');

        // Admin sponsors
        Route::get('sponrun/{sponrun}/runpart/{runpart}/sponsor/create', [AdminSponsorController::class, 'create'])->name('sponrun.runpart.sponsor.create');
        Route::post('sponrun/{sponrun}/runpart/{runpart}/sponsor', [AdminSponsorController::class, 'store'])->name('sponrun.runpart.sponsor.store');
        Route::get('sponrun/{sponrun}/runpart/{runpart}/sponsor/{sponsor}/edit', [AdminSponsorController::class, 'edit'])->name('sponrun.runpart.sponsor.edit');
        Route::patch('sponrun/{sponrun}/runpart/{runpart}/sponsor/{sponsor}', [AdminSponsorController::class, 'update'])->name('sponrun.runpart.sponsor.update');
        Route::delete('sponrun/{sponrun}/runpart/{runpart}/sponsor/{sponsor}', [AdminSponsorController::class, 'destroy'])->name('sponrun.runpart.sponsor.destroy');

        // Projects
        Route::resource('project', ProjectController::class);

        // Projectlists
        Route::get('projectlist', [ProjectlistController::class, 'index'])->name('projectlist.index');
        Route::get('projectlist/create', [ProjectlistController::class, 'create'])->name('projectlist.create');
        Route::post('projectlist', [ProjectlistController::class, 'store'])->name('projectlist.store');
        Route::get('projectlist/{projectlist}/edit', [ProjectlistController::class, 'edit'])->name('projectlist.edit');
        Route::patch('projectlist/{projectlist}', [ProjectlistController::class, 'update'])->name('projectlist.update');
        Route::delete('projectlist/{projectlist}', [ProjectlistController::class, 'destroy'])->name('projectlist.destroy');
        Route::patch('projectlist/{projectlist}/projects/add', [ProjectlistController::class, 'addProjects'])->name('projectlist.addProjects');
        Route::patch('projectlist/{projectlist}/projects/remove', [ProjectlistController::class, 'removeProjects'])->name('projectlist.removeProjects');
    });

require __DIR__.'/auth.php';
