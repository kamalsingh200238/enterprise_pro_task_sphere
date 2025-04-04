<?php

namespace App\Http\Controllers\Auth;

use App\Enums\FlashMessageType;
use App\Enums\FlashMessageVariant;
use App\Enums\UserRole;
use App\Helpers\FlashMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\OAuthStatus;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function authentikRedirect()
    {
        // Check if OAuth is enabled before redirecting
        if (! OAuthStatus::isEnabled()) {
            return redirect()->route('login')
                ->with('flash', new FlashMessage(
                    'OAuth is disabled',
                    FlashMessageVariant::Error,
                    FlashMessageType::Normal,
                )->toArray());
        }

        return Socialite::driver('authentik')->redirect();
    }

    public function authentikCallback()
    {
        if (! OAuthStatus::isEnabled()) {
            return redirect()->route('login')
                ->with('flash', new FlashMessage(
                    'OAuth is disabled',
                    FlashMessageVariant::Error,
                    FlashMessageType::Normal,
                )->toArray());
        }

        try {
            $authentikUser = Socialite::driver('authentik')->user();

            $user = User::where('email', $authentikUser->getEmail())->first();

            if (! $user) {
                $user = User::create([
                    'name' => $authentikUser->getName(),
                    'email' => $authentikUser->getEmail(),
                    'uses_oauth' => true,
                    'role' => UserRole::Staff,
                ]);
            } else {
                if ($user->uses_oauth) {
                    $user->updateQuietly([
                        'name' => $authentikUser->getName(),
                        'email' => $authentikUser->getEmail(),
                        'oauth_id' => $authentikUser->getId(),
                    ]);
                } else {
                    return to_route('login')
                        ->with('flash', new FlashMessage(
                            'Please use your username and password instead of OAuth.',
                            FlashMessageVariant::Error,
                            FlashMessageType::Normal,
                        )->toArray());
                }
            }
            Auth::login($user, true);

            return redirect('/dashboard');
        } catch (\Exception $e) {
            return to_route('login')
                ->with('flash', new FlashMessage(
                    'Authentication failed',
                    FlashMessageVariant::Error,
                    FlashMessageType::Normal,
                )->toArray());
        }
    }
}
