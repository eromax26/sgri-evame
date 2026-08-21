<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon ticket - {{ $ticket->numero_ticket }}</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #eef1f5;
        }
        .barre-outils {
            max-width: 340px;
            margin: 0 auto 15px;
            text-align: center;
        }
        .ticket-repas {
            width: 340px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.08);
            padding: 14px;
            box-sizing: border-box;
        }
        .ticket-repas .cadre {
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 14px;
        }
        .ticket-repas .corps {
            display: flex;
            gap: 14px;
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
            margin-bottom: 8px;
        }
        .ticket-repas .logo-mini {
            height: 22px;
        }
        .ticket-repas .label-repas {
            font-size: 9px;
            letter-spacing: 0.06em;
            color: #94a3b8;
            text-transform: uppercase;
            font-weight: 700;
        }
        .ticket-repas .nom {
            font-weight: 700;
            font-size: 15px;
            color: #0f172a;
        }
        .ticket-repas .matricule {
            font-weight: 400;
            color: #94a3b8;
            font-size: 12px;
        }
        .ticket-repas .ligne {
            font-size: 12px;
            color: #4a6f95;
            margin-top: 3px;
        }
        .ticket-repas .numero {
            font-family: 'Courier New', monospace;
            font-size: 11.5px;
            color: #b45309;
            margin-top: 10px;
        }
        @media print {
            body { background-color: #ffffff; padding: 0; }
            .barre-outils { display: none; }
            .ticket-repas { box-shadow: none; }
        }
    </style>
</head>
<body>

<div class="barre-outils">
    <button onclick="window.print()">Imprimer</button>
    <a href="{{ route('selection.tickets') }}">Retour a mes tickets</a>
</div>

<div class="ticket-repas">
    <div class="cadre">
        <div class="corps">
            <div class="qr" id="qr-code"></div>
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

<script>
    new QRCode(document.getElementById('qr-code'), {
        text: @json($ticket->numero_ticket),
        width: 90,
        height: 90,
    });
</script>

</body>
</html>
