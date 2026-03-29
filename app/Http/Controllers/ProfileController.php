<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
            'avatar_cropped_data' => ['nullable', 'string'],
        ]);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        $avatarUrl = $this->storeAvatar($request, $user->avatar);
        if ($avatarUrl) {
            $payload['avatar'] = $avatarUrl;
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

    private function storeAvatar(Request $request, ?string $oldAvatarUrl = null): ?string
    {
        if ($request->filled('avatar_cropped_data')) {
            $data = $request->string('avatar_cropped_data')->toString();

            if (preg_match('/^data:image\/(\w+);base64,/', $data, $matches)) {
                $extension = strtolower($matches[1]);
                if ($extension === 'jpeg') {
                    $extension = 'jpg';
                }

                if (! in_array($extension, ['jpg', 'png', 'webp'], true)) {
                    return null;
                }

                $binary = base64_decode(substr($data, strpos($data, ',') + 1), true);
                if ($binary === false) {
                    return null;
                }

                $path = 'avatars/users/' . Str::uuid() . '.' . $extension;
                Storage::disk('public')->put($path, $binary);
                $this->deleteStoredAvatarIfNeeded($oldAvatarUrl);

                return Storage::disk('public')->url($path);
            }
        }

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars/users', 'public');
            $this->deleteStoredAvatarIfNeeded($oldAvatarUrl);

            return Storage::disk('public')->url($path);
        }

        return null;
    }
}
