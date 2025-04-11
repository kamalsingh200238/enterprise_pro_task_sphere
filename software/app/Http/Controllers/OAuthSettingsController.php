<?php

namespace App\Http\Controllers;

use App\Enums\FlashMessageType;
use App\Enums\FlashMessageVariant;
use App\Helpers\FlashMessage;
use App\Models\OAuthStatus;
use Gate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OAuthSettingsController extends Controller
{
    /**
     * Display the OAuth settings page.
     */
    public function show()
    {
        // Check if user has permission
        Gate::authorize('manage-oauth-settings');

        // Get current OAuth status
        $oauthEnabled = OAuthStatus::isEnabled();

        // Render the view with the current status
        return Inertia::render('settings/OAuthSettings', [
            'oauthEnabled' => $oauthEnabled,
        ]);
    }

    /**
     * Update the OAuth settings.
     */
    public function update(Request $request)
    {
        Gate::authorize('manage-oauth-settings');

        // Validate request data
        $validated = $request->validate([
            'enabled' => 'required|boolean',
        ]);

        // Update the OAuth status
        $success = OAuthStatus::toggle($validated['enabled']);

        activity()
            ->performedOn(OAuthStatus::first())
            ->causedBy(auth()->user())
            ->withProperties([
                'attributes' => [
                    'enabled' => $success ? 'true' : 'false',
                ],
                'old' => [
                    'enabled' => ! $success ? 'true' : 'false',
                ],
            ])
            ->event('oauth setting changed')
            ->log('oauth setting changed');

        if ($success) {
            return redirect()->back()
                ->with('flash', new FlashMessage(
                    'OAuth is now enabled',
                    FlashMessageVariant::Success,
                    FlashMessageType::Normal,
                )->toArray());
        }

        return back()
            ->with('flash', new FlashMessage(
                'OAuth is now disabled',
                FlashMessageVariant::Success,
                FlashMessageType::Normal,
            )->toArray());
    }
}
