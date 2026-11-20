<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user()->load(['department', 'company']);
        $nameParts = preg_split('/\s+/', trim($user->name), 2) ?: [];

        return view('admin.settings.profile', [
            'user' => $user,
            'firstName' => $nameParts[0] ?? '',
            'lastName' => $nameParts[1] ?? '',
            'username' => $user->username ?: Str::before($user->email, '@'),
            'companies' => Company::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'company_id' => ['nullable', Rule::exists('companies', 'id')],
            'department_id' => ['nullable', Rule::exists('departments', 'id')],
        ]);
        $name = trim($validated['first_name'].' '.($validated['last_name'] ?? ''));

        $user->fill([
            'name' => $name,
            'company_id' => $validated['company_id'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
        ])->save();

        return redirect()->route('admin.settings.profile')->with('status', 'Your profile was updated successfully.');
    }

    public function updateAccount(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'max:2048'],
        ]);

        $previousPhotoPath = $user->profile_photo_path;
        $newPhotoPath = null;
        if ($request->hasFile('profile_photo')) {
            $newPhotoPath = $request->file('profile_photo')->store('profile-photos', 'public');
            if ($newPhotoPath === false) {
                return back()
                    ->withErrors(['profile_photo' => 'The profile photo could not be uploaded.'])
                    ->withInput($request->except('profile_photo'));
            }
        }

        $emailChanged = $user->email !== $validated['email'];
        $user->username = $validated['username'];
        if ($emailChanged) {
            $user->email_verified_at = null;
        }
        $user->email = $validated['email'];
        if ($newPhotoPath !== null) {
            $user->profile_photo_path = $newPhotoPath;
        }
        $user->save();

        if ($newPhotoPath !== null && $previousPhotoPath !== null
            && Storage::disk('public')->exists($previousPhotoPath)
            && ! Storage::disk('public')->delete($previousPhotoPath)) {
            Log::warning('A replaced profile photo could not be deleted.', ['user_id' => $user->id]);
        }

        $status = 'Your account information was updated successfully.';
        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
            $status .= ' A verification email has been sent to your new email address.';
        }

        return redirect()->route('admin.settings.profile')->with('status', $status);
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
