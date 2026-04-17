<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Remove campos de imagem do $data para não sobrescrever com null
        // Só coloca de volta se um novo arquivo foi enviado
        unset($data['avatar'], $data['cover_photo']);

        if ($request->hasFile('avatar')) {
            if ($request->user()->avatar) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete(str_replace('storage/', '', $request->user()->avatar));
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = 'storage/' . $path;
        }

        if ($request->hasFile('cover_photo')) {
            if ($request->user()->cover_photo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete(str_replace('storage/', '', $request->user()->cover_photo));
            }
            $path = $request->file('cover_photo')->store('covers', 'public');
            $data['cover_photo'] = 'storage/' . $path;
        }

        $request->user()->fill($data);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current-password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
