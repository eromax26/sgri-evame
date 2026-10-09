{{--
    Pastille de statut d'une commande.
    Les classes disponibles dans sgri.css sont : sg-pill--off, --warn, --info, --ok, --danger.
--}}
@php
    $statuts = [
        'brouillon' => ['Brouillon', 'sg-pill--warn'],
        'validee' => ['Validée', 'sg-pill--info'],
        'livree_partielle' => ['Livrée partiellement', 'sg-pill--warn'],
        'livree' => ['Livrée', 'sg-pill--ok'],
        'cloturee' => ['Clôturée', 'sg-pill--off'],
        'annulee' => ['Annulée', 'sg-pill--danger'],
    ];
    [$libelleStatut, $classeStatut] = $statuts[$commande->statut] ?? [$commande->statut, 'sg-pill--off'];
@endphp
<span class="sg-pill {{ $classeStatut }}">{{ $libelleStatut }}</span>
