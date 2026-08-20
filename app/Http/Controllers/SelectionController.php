<?php

namespace App\Http\Controllers;

use App\Models\LigneMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SelectionController extends Controller
{
    public function index()
    {
        $lignesDisponibles = LigneMenu::with('plat', 'menu')
            ->whereHas('menu', fn ($q) => $q->where('statut_publication', 'publie'))
            ->where('statut', 'prevu')
            ->whereNull('collaborateur_id')
            ->where('date_repas', '>=', now()->toDateString())
            ->orderBy('date_repas')
            ->get();

        return view('selection.index', compact('lignesDisponibles'));
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
            // Verrou pessimiste : empeche deux collaborateurs de valider la meme ligne en meme temps
            $nbValidees += DB::transaction(function () use ($ligneId, $collaborateur) {
                $ligne = LigneMenu::where('id', $ligneId)
                    ->where('statut', 'prevu')
                    ->whereNull('collaborateur_id')
                    ->where('date_repas', '>=', now()->toDateString())
                    ->whereHas('menu', fn ($q) => $q->where('statut_publication', 'publie'))
                    ->lockForUpdate()
                    ->first();

                if (! $ligne) {
                    return 0;
                }

                $ligne->update([
                    'collaborateur_id' => $collaborateur->id,
                    'date_selection' => now(),
                    'statut' => 'demande',
                ]);

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

        $repas = LigneMenu::with('plat')
            ->where('collaborateur_id', $collaborateur->id)
            ->where('statut', 'consomme')
            ->whereRaw("DATE_FORMAT(date_repas, '%Y-%m') = ?", [$periode])
            ->orderBy('date_repas', 'desc')
            ->get();

        $totalMontant = $repas->sum('prix');

        return view('selection.historique', compact('repas', 'periode', 'totalMontant'));
    }

    public function mesTickets()
    {
        $collaborateur = Auth::user();

        $tickets = LigneMenu::with('plat')
            ->where('collaborateur_id', $collaborateur->id)
            ->whereIn('statut', ['demande', 'imprime'])
            ->orderBy('date_repas')
            ->get();

        return view('selection.tickets', compact('tickets'));
    }
}
