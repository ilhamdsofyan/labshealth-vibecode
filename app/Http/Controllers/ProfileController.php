<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        return view('profile.index', [
            'user' => auth()->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'avatar' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ]);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if ($request->hasFile('avatar')) {
            $this->deleteStoredAvatarIfNeeded($user->avatar);
            $path = $request->file('avatar')->store('avatars/users', 'public');
            $payload['avatar'] = Storage::disk('public')->url($path);
        }

        $user->update($payload);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $rules = [
            'password' => ['required', 'confirmed', Password::min(8)],
        ];

        if (filled($user->password)) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $validated = $request->validate($rules, [
            'current_password.current_password' => 'Password saat ini tidak cocok.',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }

    public function removeAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();

        $this->deleteStoredAvatarIfNeeded($user->avatar);

        $user->update([
            'avatar' => null,
        ]);

        return back()->with('success', 'Avatar berhasil dihapus.');
    }

    private function deleteStoredAvatarIfNeeded(?string $avatarUrl): void
    {
        if (! filled($avatarUrl)) {
            return;
        }

        $storagePrefix = Storage::disk('public')->url('');
        if (! str_starts_with($avatarUrl, $storagePrefix)) {
            return;
        }

        $path = ltrim(str_replace($storagePrefix, '', $avatarUrl), '/');
        if ($path !== '' && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
