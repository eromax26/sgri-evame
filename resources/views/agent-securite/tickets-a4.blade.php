<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Impression des tickets</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 10mm;
            background-color: #ffffff;
        }
        .grille {
            display: flex;
            flex-wrap: wrap;
        }
        .ticket-repas {
            width: 85mm;
            background-color: #ffffff;
            border-radius: 4mm;
            box-shadow: 0 0.5mm 2mm rgba(15, 23, 42, 0.08);
            padding: 3mm;
            margin: 0 3mm 3mm 0;
            box-sizing: border-box;
            page-break-inside: avoid;
        }
        .ticket-repas .cadre {
            border: 0.3mm dashed #cbd5e1;
            border-radius: 3mm;
            padding: 3mm;
        }
        .ticket-repas .corps {
            display: flex;
            gap: 3mm;
        }
        .ticket-repas .qr {
            flex-shrink: 0;
            line-height: 0;
        }
        .ticket-repas .infos {
            flex: 1;
            min-width: 0;
        }
        .ticket-repas .entete-mini {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2mm;
        }
        .ticket-repas .logo-mini {
            height: 5mm;
        }
        .ticket-repas .label-repas {
            font-size: 7.5px;
            letter-spacing: 0.06em;
            color: #94a3b8;
            text-transform: uppercase;
            font-weight: 700;
        }
        .ticket-repas .nom {
            font-weight: 700;
            font-size: 11px;
            color: #0f172a;
        }
        .ticket-repas .matricule {
            font-weight: 400;
            color: #94a3b8;
            font-size: 9px;
        }
        .ticket-repas .ligne {
            font-size: 9px;
            color: #4a6f95;
            margin-top: 1mm;
        }
        .ticket-repas .numero {
            font-family: 'Courier New', monospace;
            font-size: 8.5px;
            color: #b45309;
            margin-top: 2mm;
        }
        .barre-outils {
            padding: 10px;
            background-color: #f0f0f0;
            text-align: center;
            margin: -10mm -10mm 10mm;
        }
        @media print {
            .barre-outils { display: none; }
            .ticket-repas { box-shadow: none; }
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
        <div class="ticket-repas">
            <div class="cadre">
                <div class="corps">
                    <div class="qr" id="qr-{{ $ticket->id }}"></div>
                    <div class="infos">
                        <div class="entete-mini">
                            <img src="{{ asset('images/logo-evame-sidebar.png') }}" alt="EVAME" class="logo-mini">
                            <span class="label-repas">Ticket repas</span>
                        </div>
                        <div class="nom">
                            {{ $ticket->collaborateur->nom }} {{ $ticket->collaborateur->prenom }}
                            <span class="matricule">({{ $ticket->collaborateur->matricule }})</span>
                        </div>
                        <div class="ligne">{{ ucfirst($ticket->date_repas->translatedFormat('l d/m/Y')) }}</div>
                        <div class="ligne">{{ $ticket->plat->libelle }}</div>
                    </div>
                </div>
                <div class="numero">{{ $ticket->numero_ticket }}</div>
            </div>
        </div>
    @endforeach
</div>

<script>
    @foreach ($tickets as $ticket)
        new QRCode(document.getElementById('qr-{{ $ticket->id }}'), {
            text: @json($ticket->numero_ticket),
            width: 65,
            height: 65,
        });
    @endforeach
</script>

</body>
</html>
