<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Collaborateur;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{
    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reinitialiser-mot-de-passe', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function reset(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::broker('collaborateurs')->reset(
            $validated,
            function (Collaborateur $collaborateur, string $password) {
                $collaborateur->update([
                    'password' => $password,
                    'tentatives_echouees' => 0,
                ]);

                event(new PasswordReset($collaborateur));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Votre mot de passe a été réinitialisé. Vous pouvez vous connecter.');
        }

        $message = match ($status) {
            Password::INVALID_USER => "Aucun compte n'est associé à cette adresse e-mail.",
            Password::INVALID_TOKEN => 'Ce lien de réinitialisation est invalide ou a expiré.',
            default => 'Impossible de réinitialiser le mot de passe pour le moment.',
        };

        return back()->withErrors(['email' => $message]);
    }
}
