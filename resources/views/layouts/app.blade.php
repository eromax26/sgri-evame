<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SGRI - @yield('titre')</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@600;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="{{ asset('css/sgri.css') }}" rel="stylesheet">
</head>
<body>

<div class="sg-sidebar">
    <div class="sg-sidebar-logo">
        <img src="{{ asset('images/logo-evame-sidebar.png') }}" alt="EVAME" style="height: 34px;">
        <div class="sg-sidebar-logo-sub">CANTINE EVAME</div>
    </div>

    <nav class="sg-sidebar-nav">
    @if (auth()->user()->aLeRole('Administrateur DSII'))
        <div class="sg-nav-title">Administration</div>
        <a href="{{ route('tableau-bord.index') }}" class="sg-nav-link {{ request()->routeIs('tableau-bord.*') ? 'sg-active' : '' }}">Dashboard</a>
        <a href="{{ route('collaborateurs.index') }}" class="sg-nav-link {{ request()->routeIs('collaborateurs.*') ? 'sg-active' : '' }}">Collaborateurs</a>
        <a href="{{ route('roles.index') }}" class="sg-nav-link {{ request()->routeIs('roles.*') ? 'sg-active' : '' }}">Roles</a>
        <a href="{{ route('societes.index') }}" class="sg-nav-link {{ request()->routeIs('societes.*') ? 'sg-active' : '' }}">Sociétés</a>
        <a href="{{ route('departements.index') }}" class="sg-nav-link {{ request()->routeIs('departements.*') ? 'sg-active' : '' }}">Départements</a>
    @endif

    @if (auth()->user()->aLeRole('Collaborateur'))
        <div class="sg-nav-title">Mon espace</div>
        <a href="{{ route('selection.index') }}" class="sg-nav-link {{ request()->routeIs('selection.index') ? 'sg-active' : '' }}">Menu de la semaine</a>
        <a href="{{ route('selection.tickets') }}" class="sg-nav-link {{ request()->routeIs('selection.tickets') ? 'sg-active' : '' }}">Mes tickets</a>
        <a href="{{ route('selection.historique') }}" class="sg-nav-link {{ request()->routeIs('selection.historique') ? 'sg-active' : '' }}">Historique des repas</a>
    @endif

    @if (auth()->user()->aLeRole('Responsable cantine'))
        <div class="sg-nav-title">Restauration</div>
        <a href="{{ route('plats.index') }}" class="sg-nav-link {{ request()->routeIs('plats.*') ? 'sg-active' : '' }}">Catalogue des plats</a>
        <a href="{{ route('menus.index') }}" class="sg-nav-link {{ request()->routeIs('menus.index') ? 'sg-active' : '' }}">Planification du menu</a>
        <a href="{{ route('menus.previsions') }}" class="sg-nav-link {{ request()->routeIs('menus.previsions') ? 'sg-active' : '' }}">Prévisions de préparation</a>

        <div class="sg-nav-title">Gestion du stock</div>
        <a href="{{ route('articles.index') }}" class="sg-nav-link {{ request()->routeIs('articles.*') ? 'sg-active' : '' }}">Articles</a>
        <a href="{{ route('mouv-stocks.index') }}" class="sg-nav-link {{ request()->routeIs('mouv-stocks.*') ? 'sg-active' : '' }}">Mouvements de stock</a>

        <div class="sg-nav-title">Pilotage</div>
        <a href="{{ route('tableau-bord.index') }}" class="sg-nav-link {{ request()->routeIs('tableau-bord.*') ? 'sg-active' : '' }}">Statistiques</a>
    @endif

   @if (auth()->user()->aLeRole('Agent de securite'))
        <div class="sg-nav-title">Tickets</div>
        <a href="{{ route('agent.demandes') }}" class="sg-nav-link {{ request()->routeIs('agent.demandes') ? 'sg-active' : '' }}">Demandes en attente</a>
        <a href="{{ route('agent.verifierForm') }}" class="sg-nav-link {{ request()->routeIs('agent.verifierForm') ? 'sg-active' : '' }}">Vérifier un ticket</a>
        <a href="{{ route('agent.journal') }}" class="sg-nav-link {{ request()->routeIs('agent.journal') ? 'sg-active' : '' }}">Journal des passages</a>
    @endif

    @if (auth()->user()->aLeRole('Ressources Humaines'))
        <div class="sg-nav-title">Ressources humaines</div>
        <a href="{{ route('etat-rh.index') }}" class="sg-nav-link {{ request()->routeIs('etat-rh.index') ? 'sg-active' : '' }}">Etat mensuel</a>
        <a href="{{ route('etat-rh.historique') }}" class="sg-nav-link {{ request()->routeIs('etat-rh.historique') ? 'sg-active' : '' }}">Historique des états</a>
        <a href="{{ route('tableau-bord.index') }}" class="sg-nav-link {{ request()->routeIs('tableau-bord.*') ? 'sg-active' : '' }}">Statistiques</a>
    @endif

    @if (auth()->user()->aLeRole('Direction Generale'))
        <div class="sg-nav-title">Pilotage</div>
        <a href="{{ route('tableau-bord.index') }}" class="sg-nav-link {{ request()->routeIs('tableau-bord.*') ? 'sg-active' : '' }}">Tableau de bord</a>
    @endif

    <div class="sg-nav-title">Mon compte</div>
    <a href="{{ route('profil.index') }}" class="sg-nav-link {{ request()->routeIs('profil.*') ? 'sg-active' : '' }}">Mon profil</a>
    </nav>
</div>

<div class="sg-main">
    <div class="sg-topbar d-flex justify-content-between align-items-center">
        <div class="sg-breadcrumb">@yield('fil')</div>
        <div class="d-flex align-items-center gap-3">
            <div class="sg-user-info">
                <div class="sg-user-name">{{ auth()->user()->nom }} {{ auth()->user()->prenom }}</div>
                <div class="sg-user-role">
                    @foreach (auth()->user()->roles as $role)
                        {{ $role->libelle }}@if (! $loop->last), @endif
                    @endforeach
                </div>
            </div>
            <div class="sg-avatar">{{ strtoupper(substr(auth()->user()->nom, 0, 1) . substr(auth()->user()->prenom, 0, 1)) }}</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger">Déconnexion</button>
            </form>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @yield('contenu')
</div>

</body>
</html>