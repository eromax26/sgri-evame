<?php

namespace App\Http\Controllers;

use App\Models\Collaborateur;
use App\Models\LigneMenu;
use Illuminate\Http\Request;

class EtatRhController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->input('periode', now()->format('Y-m'));

        $lignes = LigneMenu::where('statut', 'consomme')
            ->whereRaw("DATE_FORMAT(date_repas, '%Y-%m') = ?", [$periode])
            ->get()
            ->groupBy('collaborateur_id');

        $etat = [];

        foreach ($lignes as $collaborateurId => $lignesCollaborateur) {
            $collaborateur = Collaborateur::find($collaborateurId);

            if (! $collaborateur) {
                continue;
            }

            $etat[] = [
                'collaborateur' => $collaborateur,
                'nb_repas' => $lignesCollaborateur->count(),
                'montant' => $lignesCollaborateur->sum('prix'),
            ];
        }

        $dejaVerrouille = LigneMenu::where('statut', 'consomme')
            ->whereRaw("DATE_FORMAT(date_repas, '%Y-%m') = ?", [$periode])
            ->where('statut_facturation', 'verrouille')
            ->exists();

        return view('etat-rh.index', compact('etat', 'periode', 'dejaVerrouille'));
    }

    public function verrouiller(Request $request)
    {
        $validated = $request->validate([
            'periode' => ['required', 'date_format:Y-m'],
        ]);

        $dejaVerrouille = LigneMenu::whereRaw("DATE_FORMAT(date_repas, '%Y-%m') = ?", [$validated['periode']])
            ->where('statut_facturation', 'verrouille')
            ->exists();

        if ($dejaVerrouille) {
            return back()->with('error', 'Cette periode a deja ete verrouillee.');
        }

        LigneMenu::where('statut', 'consomme')
            ->whereRaw("DATE_FORMAT(date_repas, '%Y-%m') = ?", [$validated['periode']])
            ->update([
                'periode_facturation' => $validated['periode'],
                'statut_facturation' => 'verrouille',
            ]);

        return redirect()->route('etat-rh.index', ['periode' => $validated['periode']])
            ->with('success', 'Etat mensuel verrouille avec succes.');
    }

    public function historique()
    {
        $periodes = LigneMenu::where('statut', 'consomme')
            ->where('statut_facturation', 'verrouille')
            ->get()
            ->groupBy('periode_facturation')
            ->map(function ($lignes, $periode) {
                return [
                    'periode' => $periode,
                    'nb_collaborateurs' => $lignes->pluck('collaborateur_id')->unique()->count(),
                    'nb_repas' => $lignes->count(),
                    'montant_total' => $lignes->sum('prix'),
                ];
            })
            ->sortByDesc('periode');

        return view('etat-rh.historique', compact('periodes'));
    }
}