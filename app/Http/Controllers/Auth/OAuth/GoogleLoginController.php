<?php

namespace App\Http\Controllers\Auth\OAuth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleLoginController extends Controller
{
    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToProvider()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleProviderCallback()
    {
        try {
            $externalUser = Socialite::driver('google')->stateless()->user();
            $user = User::where('email', $externalUser->getEmail())->first();
        } catch (InvalidStateException $e) {
            $message = 'Something went wrong with the authentication, sorry please try again?';
            return redirect()->route('login')->withErrors([$message]);
        }

        if ($user === null) {
            $message = sprintf(
                'Account was not found; please register with the same e-mail address that you used to log in (%s)',
                $externalUser->getEmail()
            );
            return redirect()->route('register')->withErrors([$message]);
        }

        Auth::login($user, true);
        return redirect()->route('home');
    }
}
