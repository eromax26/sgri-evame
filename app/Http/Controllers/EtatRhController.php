<?php

namespace App\Http\Controllers;

use App\Models\Collaborateur;
use App\Models\SelectionRepas;
use App\Support\XlsxExporter;
use Illuminate\Http\Request;

class EtatRhController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->input('periode', now()->format('Y-m'));

        $lignes = SelectionRepas::where('statut', 'consomme')
            ->whereHas('ligneMenu', fn ($q) => $q->whereRaw("DATE_FORMAT(date_repas, '%Y-%m') = ?", [$periode]))
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

        $totalConsomme = SelectionRepas::where('statut', 'consomme')
            ->whereHas('ligneMenu', fn ($q) => $q->whereRaw("DATE_FORMAT(date_repas, '%Y-%m') = ?", [$periode]))
            ->count();

        $totalVerrouille = SelectionRepas::where('statut', 'consomme')
            ->whereHas('ligneMenu', fn ($q) => $q->whereRaw("DATE_FORMAT(date_repas, '%Y-%m') = ?", [$periode]))
            ->where('statut_facturation', 'verrouille')
            ->count();

        // 'aucun', 'partiel' (des repas verrouilles tardivement peuvent encore arriver) ou 'complet'
        $statutVerrouillage = match (true) {
            $totalVerrouille === 0 => 'aucun',
            $totalVerrouille < $totalConsomme => 'partiel',
            default => 'complet',
        };

        $resteAVerrouiller = $totalConsomme - $totalVerrouille;

        return view('etat-rh.index', compact('etat', 'periode', 'statutVerrouillage', 'resteAVerrouiller'));
    }

    public function verrouiller(Request $request)
    {
        $validated = $request->validate([
            'periode' => ['required', 'date_format:Y-m'],
        ]);

        // Verrouille les repas consommes pas encore couverts : permet de rattraper les
        // retraits tardifs sur une periode deja verrouillee une premiere fois (RG-facturation).
        $nbVerrouilles = SelectionRepas::where('statut', 'consomme')
            ->whereHas('ligneMenu', fn ($q) => $q->whereRaw("DATE_FORMAT(date_repas, '%Y-%m') = ?", [$validated['periode']]))
            ->where('statut_facturation', '!=', 'verrouille')
            ->update([
                'periode_facturation' => $validated['periode'],
                'statut_facturation' => 'verrouille',
            ]);

        if ($nbVerrouilles === 0) {
            return back()->with('error', 'Rien à verrouiller : tous les repas consommés de cette période le sont déjà.');
        }

        return redirect()->route('etat-rh.index', ['periode' => $validated['periode']])
            ->with('success', "{$nbVerrouilles} repas verrouille(s) pour la periode {$validated['periode']}.");
    }

    public function export(Request $request)
    {
        $periode = $request->input('periode', now()->format('Y-m'));

        $lignes = SelectionRepas::where('statut', 'consomme')
            ->whereHas('ligneMenu', fn ($q) => $q->whereRaw("DATE_FORMAT(date_repas, '%Y-%m') = ?", [$periode]))
            ->get()
            ->groupBy('collaborateur_id');

        $lignesExport = [];

        foreach ($lignes as $collaborateurId => $lignesCollaborateur) {
            $collaborateur = Collaborateur::find($collaborateurId);

            if (! $collaborateur) {
                continue;
            }

            $lignesExport[] = [
                $collaborateur->matricule,
                $collaborateur->nom,
                $collaborateur->prenom,
                $lignesCollaborateur->count(),
                (float) $lignesCollaborateur->sum('prix'),
            ];
        }

        return XlsxExporter::download(
            "etat-rh-{$periode}.xlsx",
            ['Matricule', 'Nom', 'Prénom', 'Repas consommés', 'Montant à retenir (FCFA)'],
            $lignesExport
        );
    }

    /**
     * Detail des repas d'un collaborateur sur une periode : sert a justifier le
     * montant retenu sur son salaire, ligne par ligne, en cas de contestation.
     */
    public function collaborateur(Request $request, Collaborateur $collaborateur)
    {
        $periode = $request->input('periode', now()->format('Y-m'));

        // Meme filtre que l'etat mensuel (statut 'consomme') pour que le total
        // affiche ici corresponde exactement a la ligne de l'etat.
        $repas = SelectionRepas::with(['ligneMenu', 'plat'])
            ->where('collaborateur_id', $collaborateur->id)
            ->where('statut', 'consomme')
            ->whereHas('ligneMenu', fn ($q) => $q->whereRaw("DATE_FORMAT(date_repas, '%Y-%m') = ?", [$periode]))
            ->get()
            ->sortByDesc(fn ($selection) => $selection->ligneMenu->date_repas)
            ->values();

        $totalMontant = $repas->sum('prix');

        // Repas reserves mais jamais retires : n'entrent pas dans la retenue, mais
        // expliquent l'ecart entre ce que le collaborateur pense devoir et le total.
        $nbNonRetires = SelectionRepas::where('collaborateur_id', $collaborateur->id)
            ->whereIn('statut', ['demande', 'imprime'])
            ->whereHas('ligneMenu', fn ($q) => $q->whereRaw("DATE_FORMAT(date_repas, '%Y-%m') = ?", [$periode]))
            ->count();

        return view('etat-rh.collaborateur', compact(
            'collaborateur',
            'repas',
            'periode',
            'totalMontant',
            'nbNonRetires'
        ));
    }

    public function historique()
    {
        $periodes = SelectionRepas::where('statut', 'consomme')
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