<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Impression des tickets</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 10mm;
        }
        .grille {
            display: flex;
            flex-wrap: wrap;
        }
        .ticket {
            width: 60mm;
            height: 45mm;
            border: 1px dashed #999;
            padding: 4mm;
            margin: 0 3mm 3mm 0;
            box-sizing: border-box;
            page-break-inside: avoid;
        }
        .ticket .entete {
            text-align: center;
            border-bottom: 1px solid #ccc;
            padding-bottom: 3px;
            margin-bottom: 5px;
        }
        .ticket .entete .societe {
            font-size: 11px;
            font-weight: bold;
        }
        .ticket .entete .appli {
            font-size: 8px;
        }
        .ticket .ligne {
            font-size: 9.5px;
            margin-bottom: 2px;
        }
        .ticket .numero {
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            margin-top: 5px;
        }
        .barre-outils {
            padding: 10px;
            background-color: #f0f0f0;
            text-align: center;
            margin: -10mm -10mm 10mm;
        }
        @media print {
            .barre-outils { display: none; }
        }
    </style>
</head>
<body>

<div class="barre-outils">
    <button onclick="window.print()">Imprimer</button>
    <a href="{{ route('agent.demandes') }}">Retour aux demandes</a>
</div>

<div class="grille">
    @foreach ($tickets as $ticket)
        <div class="ticket">
            <div class="entete">
                <div class="societe">GROUPE EVAME</div>
                <div class="appli">Restauration interne</div>
            </div>

            <div class="ligne"><strong>{{ $ticket->collaborateur->prenom }} {{ $ticket->collaborateur->nom }}</strong></div>
            <div class="ligne">Matricule : {{ $ticket->collaborateur->matricule }}</div>
            <div class="ligne">Date : {{ $ticket->date_repas->format('d/m/Y') }}</div>
            <div class="ligne">Plat : {{ $ticket->plat->libelle }}</div>

            <div class="numero">{{ $ticket->numero_ticket }}</div>
        </div>
    @endforeach
</div>

</body>
</html>