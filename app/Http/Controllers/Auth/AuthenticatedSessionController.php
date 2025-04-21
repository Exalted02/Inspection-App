<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
	public function loginWithToken(Request $request)
    {
        // Retrieve the token from the query parameters
        $token = $request->query('token');

        try {
            // Decrypt the token to get the user ID
            $userId = Crypt::decryptString($token);

            // Find the user by their ID
            $user = User::find($userId);

            if ($user) {
                // Log the user in on Site 2
                Auth::login($user);

                // Redirect to the dashboard or any desired page
                return redirect('/dashboard');
            }
        } catch (\Exception $e) {
            // Handle invalid token
            return redirect('/login')->withErrors('Invalid login token.');
        }
    }
}
