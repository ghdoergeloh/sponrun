<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('Account/Edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'birthday' => 'required|date',
            'street' => 'required|string|max:255',
            'housenumber' => 'required|string|max:31',
            'postcode' => 'required|string|size:5',
            'city' => 'required|string|max:255',
            'gender' => 'required|in:m,f',
            'wants_newsletter' => 'nullable|boolean',
        ])->validate();

        $request->user()->update($data);

        return redirect()->route('account.edit')->with('success', 'Konto aktualisiert.');
    }
}
