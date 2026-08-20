<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.mot-de-passe-oublie');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        Password::broker('collaborateurs')->sendResetLink(
            $request->only('email')
        );

        // Meme message que l'email existe ou non, pour ne pas reveler les comptes enregistres.
        return back()->with('success', "Si cette adresse est associée à un compte, un lien de réinitialisation vient d'être envoyé.");
    }
}
