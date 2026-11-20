<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user()->load(['department', 'company']);

        return view('admin.settings.profile', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $user->fill($validated)->save();

        return redirect()->route('admin.settings.profile')->with('status', 'Your profile was updated successfully.');
    }

    public function security(Request $request): View
    {
        return view('admin.settings.security', ['user' => $request->user()]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->forceFill(['password' => $validated['password']])->save();
        $request->session()->regenerate();

        return redirect()->route('admin.settings.security')->with('status', 'Your password was changed successfully.');
    }
}
