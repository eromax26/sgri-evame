<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Impression des tickets (thermique)</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        @page {
            size: 58mm auto;
            margin: 0;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 6mm 0;
            background-color: #ffffff;
        }
        .ticket-repas {
            width: 54mm;
            margin: 0 auto 6mm;
            padding: 3mm;
            box-sizing: border-box;
            border-bottom: 1px dashed #000;
            page-break-after: always;
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
            text-transform: uppercase;
            font-weight: 700;
        }
        .ticket-repas .qr {
            text-align: center;
            margin: 2mm 0;
        }
        .ticket-repas .nom {
            font-weight: 700;
            font-size: 11px;
            text-align: center;
        }
        .ticket-repas .matricule {
            font-weight: 400;
            font-size: 9px;
        }
        .ticket-repas .ligne {
            font-size: 9px;
            text-align: center;
            margin-top: 1mm;
        }
        .ticket-repas .numero {
            font-family: 'Courier New', monospace;
            font-size: 9px;
            text-align: center;
            margin-top: 2mm;
        }
        .barre-outils {
            text-align: center;
            margin-bottom: 10px;
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

@foreach ($tickets as $ticket)
    <div class="ticket-repas">
        <div class="entete-mini">
            <img src="{{ asset('images/logo-evame-sidebar.png') }}" alt="EVAME" class="logo-mini">
            <span class="label-repas">Ticket repas</span>
        </div>

        <div class="qr" id="qr-{{ $ticket->id }}"></div>

        <div class="nom">{{ $ticket->collaborateur->nom }} {{ $ticket->collaborateur->prenom }}</div>
        <div class="matricule" style="text-align:center;">({{ $ticket->collaborateur->matricule }})</div>
        <div class="ligne">{{ ucfirst($ticket->date_repas->translatedFormat('l d/m/Y')) }}</div>
        <div class="ligne">{{ $ticket->plat->libelle }}</div>

        <div class="numero">{{ $ticket->numero_ticket }}</div>
    </div>
@endforeach

<script>
    @foreach ($tickets as $ticket)
        new QRCode(document.getElementById('qr-{{ $ticket->id }}'), {
            text: @json($ticket->numero_ticket),
            width: 85,
            height: 85,
        });
    @endforeach
</script>

</body>
</html>
