<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Impression des tickets</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .ticket {
            width: 72mm;
            padding: 5mm 3mm;
            border-bottom: 1px dashed #999;
            page-break-inside: avoid;
        }
        .ticket .entete {
            text-align: center;
            margin-bottom: 6px;
        }
        .ticket .entete .societe {
            font-size: 13px;
            font-weight: bold;
        }
        .ticket .entete .appli {
            font-size: 10px;
        }
        .ticket .ligne {
            font-size: 11px;
            margin-bottom: 3px;
        }
        .ticket .numero {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            margin-top: 6px;
            padding-top: 5px;
            border-top: 1px solid #000;
        }
        .barre-outils {
            padding: 15px;
            background-color: #f0f0f0;
            text-align: center;
        }
        @media print {
            .barre-outils { display: none; }
            .ticket { border-bottom: 1px dashed #ccc; }
        }
    </style>
</head>
<body>

<div class="barre-outils">
    <button onclick="window.print()">Imprimer</button>
    <a href="{{ route('agent.demandes') }}">Retour aux demandes</a>
</div>

@foreach ($tickets as $ticket)
    <div class="ticket">
        <div class="entete">
            <div class="societe">GROUPE EVAME</div>
            <div class="appli">Restauration interne</div>
        </div>

        <div class="ligne"><strong>{{ $ticket->collaborateur->prenom }} {{ $ticket->collaborateur->nom }}</strong></div>
        <div class="ligne">Matricule : {{ $ticket->collaborateur->matricule }}</div>
        <div class="ligne">Date du repas : {{ $ticket->date_repas->format('d/m/Y') }}</div>
        <div class="ligne">Plat : {{ $ticket->plat->libelle }}</div>

        <div class="numero">{{ $ticket->numero_ticket }}</div>
    </div>
@endforeach

</body>
</html>