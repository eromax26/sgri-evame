<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SGRI - @yield('titre')</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f7fa;
        }
        .sidebar {
            width: 240px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            overflow-y: auto;
            background-color: #ffffff;
            border-right: 1px solid #e0e0e0;
            padding-bottom: 20px;
        }
        .sidebar .zone-logo {
            padding: 16px 20px;
            border-bottom: 1px solid #e0e0e0;
        }
        .sidebar .titre-menu {
            font-size: 11px;
            padding: 14px 20px 6px;
            text-transform: uppercase;
            color: #9aa0aa;
        }
        .sidebar a {
            display: block;
            padding: 10px 20px;
            font-size: 14px;
            text-decoration: none;
            color: #4a5160;
        }
        .sidebar a:hover {
            background-color: #f7f7f8;
        }
        .sidebar a.actif {
            background-color: #fdeaea;
            color: #e31e24;
            font-weight: 600;
            border-right: 3px solid #e31e24;
        }
        .contenu {
            margin-left: 240px;
            padding: 20px 30px;
        }
        .barre-haut {
            background-color: #ffffff;
            border-bottom: 1px solid #dddddd;
            padding: 12px 30px;
            margin: -20px -30px 20px;
        }
        .info-user {
            text-align: right;
            line-height: 1.2;
        }
        .info-user .nom {
            font-size: 13px;
            font-weight: 600;
        }
        .info-user .role {
            font-size: 11px;
            color: #9aa0aa;
        }
        .fil {
            font-size: 12px;
            color: #9aa0aa;
        }
        .fil strong {
            color: #333333;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="zone-logo">
        <img src="{{ asset('images/logo-evame-sidebar.png') }}" alt="EVAME" style="height: 34px;">
        <div style="font-size: 11px; margin-top: 6px; color: #9aa0aa;">CANTINE EVAME</div>
    </div>

    @if (auth()->user()->aLeRole('Administrateur DSII'))
        <div class="titre-menu">Administration</div>
        <a href="{{ route('tableau-bord.index') }}" class="{{ request()->routeIs('tableau-bord.*') ? 'actif' : '' }}">Dashboard</a>
        <a href="{{ route('collaborateurs.index') }}" class="{{ request()->routeIs('collaborateurs.*') ? 'actif' : '' }}">Collaborateurs</a>
        <a href="{{ route('roles.index') }}" class="{{ request()->routeIs('roles.*') ? 'actif' : '' }}">Roles</a>
        <a href="{{ route('societes.index') }}" class="{{ request()->routeIs('societes.*') ? 'actif' : '' }}">Sociétés</a>
        <a href="{{ route('departements.index') }}" class="{{ request()->routeIs('departements.*') ? 'actif' : '' }}">Départements</a>
    @endif

    @if (auth()->user()->aLeRole('Collaborateur'))
        <div class="titre-menu">Mon espace</div>
        <a href="{{ route('selection.index') }}" class="{{ request()->routeIs('selection.index') ? 'actif' : '' }}">Menu de la semaine</a>
        <a href="{{ route('selection.tickets') }}" class="{{ request()->routeIs('selection.tickets') ? 'actif' : '' }}">Mes tickets</a>
        <a href="{{ route('selection.historique') }}" class="{{ request()->routeIs('selection.historique') ? 'actif' : '' }}">Historique des repas</a>
    @endif

    @if (auth()->user()->aLeRole('Responsable cantine'))
        <div class="titre-menu">Restauration</div>
        <a href="{{ route('plats.index') }}" class="{{ request()->routeIs('plats.*') ? 'actif' : '' }}">Catalogue des plats</a>
        <a href="{{ route('menus.index') }}" class="{{ request()->routeIs('menus.index') ? 'actif' : '' }}">Planification du menu</a>
        <a href="{{ route('menus.previsions') }}" class="{{ request()->routeIs('menus.previsions') ? 'actif' : '' }}">Prévisions de préparation</a>

        <div class="titre-menu">Gestion du stock</div>
        <a href="{{ route('articles.index') }}" class="{{ request()->routeIs('articles.*') ? 'actif' : '' }}">Articles</a>
        <a href="{{ route('mouv-stocks.index') }}" class="{{ request()->routeIs('mouv-stocks.*') ? 'actif' : '' }}">Mouvements de stock</a>

        <div class="titre-menu">Pilotage</div>
        <a href="{{ route('tableau-bord.index') }}" class="{{ request()->routeIs('tableau-bord.*') ? 'actif' : '' }}">Statistiques</a>
    @endif

   @if (auth()->user()->aLeRole('Agent de securite'))
        <div class="titre-menu">Tickets</div>
        <a href="{{ route('agent.demandes') }}" class="{{ request()->routeIs('agent.demandes') ? 'actif' : '' }}">Demandes en attente</a>
        <a href="{{ route('agent.verifierForm') }}" class="{{ request()->routeIs('agent.verifierForm') ? 'actif' : '' }}">Vérifier un ticket</a>
        <a href="{{ route('agent.journal') }}" class="{{ request()->routeIs('agent.journal') ? 'actif' : '' }}">Journal des passages</a>
    @endif

    @if (auth()->user()->aLeRole('Ressources Humaines'))
        <div class="titre-menu">Ressources humaines</div>
        <a href="{{ route('etat-rh.index') }}" class="{{ request()->routeIs('etat-rh.index') ? 'actif' : '' }}">Etat mensuel</a>
        <a href="{{ route('etat-rh.historique') }}" class="{{ request()->routeIs('etat-rh.historique') ? 'actif' : '' }}">Historique des états</a>
        <a href="{{ route('tableau-bord.index') }}" class="{{ request()->routeIs('tableau-bord.*') ? 'actif' : '' }}">Statistiques</a>
    @endif

    @if (auth()->user()->aLeRole('Direction Generale'))
        <div class="titre-menu">Pilotage</div>
        <a href="{{ route('tableau-bord.index') }}" class="{{ request()->routeIs('tableau-bord.*') ? 'actif' : '' }}">Tableau de bord</a>
    @endif

    <div class="titre-menu">Mon compte</div>
    <a href="{{ route('profil.index') }}" class="{{ request()->routeIs('profil.*') ? 'actif' : '' }}">Mon profil</a>
</div>

<div class="contenu">
    <div class="barre-haut d-flex justify-content-between align-items-center">
        <div class="fil">@yield('fil')</div>
        <div class="d-flex align-items-center gap-3">
            <div class="info-user">
                <div class="nom">{{ auth()->user()->nom }} {{ auth()->user()->prenom }}</div>
                <div class="role">
                    @foreach (auth()->user()->roles as $role)
                        {{ $role->libelle }}@if (! $loop->last), @endif
                    @endforeach
                </div>
            </div>
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