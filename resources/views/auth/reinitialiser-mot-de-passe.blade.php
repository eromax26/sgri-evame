<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SGRI - Réinitialiser le mot de passe</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@600;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="{{ asset('css/sgri.css') }}" rel="stylesheet">
</head>
<body>

<div class="sg-auth-wrap">
    <div class="sg-auth-side d-none d-md-flex">
        <p class="sg-eyebrow" style="color: rgba(255,255,255,.55); margin-bottom: 14px;">SGRI &middot; Restauration interne</p>
        <img src="{{ asset('images/logo-evame.png') }}" alt="EVAME" class="sg-auth-side-logo">
        <div class="sg-auth-side-title">SGRI</div>
        <div class="sg-auth-side-sub">Système de Gestion de la Restauration Interne</div>
    </div>

    <div class="sg-auth-form-col">
        <div class="sg-auth-card">
            <p class="sg-eyebrow" style="margin-bottom: 8px;">Récupération d'accès</p>
            <div class="sg-auth-title">Nouveau mot de passe</div>
            <div class="sg-auth-title-sub">Choisissez un nouveau mot de passe pour votre compte.</div>

            @if ($errors->any())
                <div class="alert alert-danger py-2 small d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill"></i>{{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label class="sg-auth-label">Adresse e-mail</label>
                    <input type="email" name="email" class="form-control sg-auth-input" value="{{ old('email', $email) }}" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="sg-auth-label">Nouveau mot de passe</label>
                    <input type="password" name="password" class="form-control sg-auth-input" required>
                    <div class="form-text">Au moins 8 caractères.</div>
                </div>
                <div class="mb-4">
                    <label class="sg-auth-label">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation" class="form-control sg-auth-input" required>
                </div>
                <button type="submit" class="btn sg-auth-btn">Réinitialiser le mot de passe</button>
            </form>

            <div class="sg-auth-footer">
                <a href="{{ route('login') }}" style="color: var(--sg-bleu-marine); font-weight: 600;">Retour à la connexion</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
