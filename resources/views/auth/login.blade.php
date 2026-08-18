<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SGRI - Connexion</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f5f5f5; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <div class="text-center mb-4">
                        <img src="{{ asset('images/logo-evame.png') }}" alt="EVAME" style="height: 90px;">
                        <div class="mt-2 fw-bold">SGRI</div>
                        <div class="text-muted" style="font-size: 12px;">Gestion de la Restauration Interne</div>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger py-2 small">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Identifiant</label>
                            <input type="text" name="identifiant" class="form-control" value="{{ old('identifiant') }}" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn w-100 text-white" style="background-color: #e31e24;">Se connecter</button>
                    </form>

                </div>
                <div class="card-footer bg-white text-center text-muted border-0 pb-3" style="font-size: 11.5px;">
                    Compte bloqué après 3 echecs ?- Contactez l'Administrateur DSII
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>