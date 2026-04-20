<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'firstname'        => 'required|string|max:255',
            'lastname'         => 'required|string|max:255',
            'email'            => 'required|string|lowercase|email|max:255|unique:users',
            'password'         => ['required', 'confirmed', Rules\Password::defaults()],
            'phone'            => 'nullable|string|max:255',
            'birthday'         => 'required|date',
            'street'           => 'required|string|max:255',
            'housenumber'      => 'required|string|max:31',
            'postcode'         => 'required|string|size:5',
            'city'             => 'required|string|max:255',
            'gender'           => 'required|in:m,f',
            'wants_newsletter' => 'nullable|boolean',
        ]);

        $user = User::create([
            'firstname'        => $request->firstname,
            'lastname'         => $request->lastname,
            'email'            => $request->email,
            'password'         => Hash::make($request->password),
            'phone'            => $request->phone,
            'birthday'         => $request->birthday,
            'street'           => $request->street,
            'housenumber'      => $request->housenumber,
            'postcode'         => $request->postcode,
            'city'             => $request->city,
            'gender'           => $request->gender,
            'wants_newsletter' => $request->wants_newsletter,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
