<?php

namespace App\Http\Controllers\Settings;

use App\Enums\FlashMessageType;
use App\Enums\FlashMessageVariant;
use App\Helpers\FlashMessage;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class PasswordController extends Controller
{
    /**
     * Show the user's password settings page.
     */
    public function edit(Request $request)
    {
        if (auth()->user()->uses_oauth) {
            return redirect()->route('profile.view')
                ->with('flash', new FlashMessage(
                    'You cannot change your password, you are an OAuth user.',
                    FlashMessageVariant::Error,
                    FlashMessageType::Normal,
                )->toArray());
        }

        return Inertia::render('settings/Password', [
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        if (auth()->user()->uses_oauth) {
            return redirect()->route('profile.view')
                ->with('flash', new FlashMessage(
                    'You cannot change your password, you are an OAuth user.',
                    FlashMessageVariant::Error,
                    FlashMessageType::Normal,
                )->toArray());
        }

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back();
    }
}
