<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'isAdmin' => $user->hasRole('admin'),
                    'wants_newsletter' => $user->wants_newsletter,
                ] : null,
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
            'appName' => config('app.name'),
            'newsletterOptional' => (bool) config('app.newsletter_optional', false),
            'urlImpressum' => config('app.url_impressum'),
            'urlPrivacy' => config('app.url_privacy_statement'),
        ];
    }
}
