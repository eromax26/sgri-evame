<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SGRI - Connexion</title>
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
        <p class="sg-eyebrow" style="color: rgba(255,255,255,.55); margin-bottom: 14px;">SGRI &middot; CANTINE EVAME</p>
        <img src="{{ asset('images/logo-evame.png') }}" alt="EVAME" class="sg-auth-side-logo">
        <div class="sg-auth-side-title">SGRI</div>
        <div class="sg-auth-side-sub">Système de Gestion de la Restauration Interne</div>
    </div>

    <div class="sg-auth-form-col">
        <div class="sg-auth-card">
            <p class="sg-eyebrow" style="margin-bottom: 8px;">Accès réservé au personnel</p>
            <div class="sg-auth-title">Connexion</div>
            <div class="sg-auth-title-sub">Accédez à votre espace SGRI</div>

            @if ($errors->any())
                <div class="alert alert-danger py-2 small d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill"></i>{{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label class="sg-auth-label">Identifiant</label>
                    <input type="text" name="identifiant" class="form-control sg-auth-input" value="{{ old('identifiant') }}" required autofocus>
                </div>
                <div class="mb-2">
                    <label class="sg-auth-label">Mot de passe</label>
                    <input type="password" name="password" class="form-control sg-auth-input" required>
                </div>
                <div class="mb-4 text-end">
                    <a href="{{ route('password.request') }}" style="font-size: 12.5px; color: var(--sg-bleu-marine); font-weight: 600;">Mot de passe oublié ?</a>
                </div>
                <button type="submit" class="btn sg-auth-btn">Se connecter</button>
            </form>

            <div class="sg-auth-footer">
                Compte bloqué après 3 échecs ? Contactez l'Administrateur DSII
            </div>
        </div>
    </div>
</div>

</body>
</html>
