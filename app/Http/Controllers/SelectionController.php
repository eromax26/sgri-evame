<?php

namespace App\Http\Controllers;

use App\Models\LigneMenu;
use App\Models\Menu;
use App\Models\SelectionRepas;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SelectionController extends Controller
{
    public function index(Request $request)
    {
        $collaborateur = Auth::user();

        $debutSemaine = $request->filled('semaine')
            ? Carbon::parse($request->input('semaine'))->startOfWeek(Carbon::MONDAY)
            : now()->startOfWeek(Carbon::MONDAY);

        $menu = Menu::where('statut_publication', 'publie')
            ->whereDate('date_debut_semaine', $debutSemaine->toDateString())
            ->first();

        $lignesSemaine = $menu
            ? LigneMenu::with('plat')
                ->where('menu_id', $menu->id)
                ->get()
                ->keyBy(fn ($ligne) => $ligne->date_repas->toDateString())
            : collect();

        $mesSelections = $lignesSemaine->isNotEmpty()
            ? SelectionRepas::where('collaborateur_id', $collaborateur->id)
                ->whereIn('ligne_menu_id', $lignesSemaine->pluck('id'))
                ->get()
                ->keyBy('ligne_menu_id')
            : collect();

        $jours = collect(range(0, 4))->map(function ($i) use ($debutSemaine, $lignesSemaine, $mesSelections) {
            $date = $debutSemaine->copy()->addDays($i);
            $ligne = $lignesSemaine->get($date->toDateString());

            return [
                'date' => $date,
                'ligne' => $ligne,
                'maSelection' => $ligne ? $mesSelections->get($ligne->id) : null,
            ];
        });

        return view('selection.index', [
            'menu' => $menu,
            'jours' => $jours,
            'debutSemaine' => $debutSemaine,
            'finSemaine' => $debutSemaine->copy()->addDays(4),
            'semainePrecedente' => $debutSemaine->copy()->subWeek()->toDateString(),
            'semaineSuivante' => $debutSemaine->copy()->addWeek()->toDateString(),
        ]);
    }

    public function valider(Request $request)
    {
        $collaborateur = Auth::user();

        if (! $collaborateur->estActif()) {
            return back()->with('error', 'Votre compte est inactif, vous ne pouvez pas selectionner de repas.');
        }

        $validated = $request->validate([
            'lignes' => ['required', 'array', 'min:1'],
            'lignes.*' => ['exists:ligne_menus,id'],
        ]);

        $nbValidees = 0;

        foreach ($validated['lignes'] as $ligneId) {
            $nbValidees += DB::transaction(function () use ($ligneId, $collaborateur) {
                $ligne = LigneMenu::where('id', $ligneId)
                    ->whereNotNull('plat_id')
                    ->where('date_repas', '>=', now()->toDateString())
                    ->whereHas('menu', fn ($q) => $q->where('statut_publication', 'publie'))
                    ->first();

                if (! $ligne) {
                    return 0;
                }

                $dejaSelectionne = SelectionRepas::where('ligne_menu_id', $ligne->id)
                    ->where('collaborateur_id', $collaborateur->id)
                    ->exists();

                if ($dejaSelectionne) {
                    return 0;
                }

                try {
                    SelectionRepas::create([
                        'ligne_menu_id' => $ligne->id,
                        'collaborateur_id' => $collaborateur->id,
                        'date_selection' => now(),
                        'statut' => 'demande',
                    ]);
                } catch (QueryException $e) {
                    // Contrainte unique (ligne_menu_id, collaborateur_id) : deja selectionne entre-temps.
                    return 0;
                }

                return 1;
            });
        }

        if ($nbValidees === 0) {
            return back()->with('error', 'Aucune selection n\'a pu etre validee (peut-etre deja prise par quelqu\'un d\'autre).');
        }

        return redirect()->route('selection.index')
            ->with('success', "{$nbValidees} repas selectionne(s). Votre demande de ticket a ete transmise a l'Agent de securite.");
    }

    public function historique(Request $request)
    {
        $collaborateur = Auth::user();
        $periode = $request->input('periode', now()->format('Y-m'));

        $repas = SelectionRepas::with('ligneMenu.plat')
            ->where('collaborateur_id', $collaborateur->id)
            ->where('statut', 'consomme')
            ->whereHas('ligneMenu', fn ($q) => $q->whereRaw("DATE_FORMAT(date_repas, '%Y-%m') = ?", [$periode]))
            ->get()
            ->sortByDesc(fn ($selection) => $selection->ligneMenu->date_repas)
            ->values();

        $totalMontant = $repas->sum('prix');

        return view('selection.historique', compact('repas', 'periode', 'totalMontant'));
    }

    public function mesTickets()
    {
        $collaborateur = Auth::user();

        $tickets = SelectionRepas::with('ligneMenu.plat')
            ->where('collaborateur_id', $collaborateur->id)
            ->whereIn('statut', ['demande', 'imprime'])
            ->get()
            ->sortBy(fn ($selection) => $selection->ligneMenu->date_repas)
            ->values();

        return view('selection.tickets', compact('tickets'));
    }

    public function imprimerTicket(SelectionRepas $selection)
    {
        abort_unless($selection->collaborateur_id === Auth::id(), 403);
        abort_unless(in_array($selection->statut, ['demande', 'imprime']), 403);

        if ($selection->statut === 'demande') {
            $selection->update([
                'numero_ticket' => 'TCK-' . Str::upper(Str::random(8)),
                'date_impression' => now(),
                'statut' => 'imprime',
            ]);
        }

        $selection->load('ligneMenu.plat', 'collaborateur');

        return view('selection.ticket-impression', ['ticket' => $selection]);
    }
}
