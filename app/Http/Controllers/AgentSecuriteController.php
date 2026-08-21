<?php

namespace App\Http\Controllers;

use App\Models\LigneMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AgentSecuriteController extends Controller
{
    public function demandesEnAttente()
    {
        $demandes = LigneMenu::with('plat', 'collaborateur')
            ->where('statut', 'demande')
            ->orderBy('date_repas')
            ->get();

        return view('agent-securite.demandes', compact('demandes'));
    }

    public function imprimer(Request $request)
    {
        $validated = $request->validate([
            'lignes' => ['required', 'array', 'min:1'],
            'lignes.*' => ['exists:ligne_menus,id'],
            'format' => ['required', 'in:thermique,a4'],
        ]);

        $agent = Auth::user();
        $idsImprimes = [];

        foreach ($validated['lignes'] as $ligneId) {
            $ligne = LigneMenu::where('id', $ligneId)
                ->where('statut', 'demande')
                ->first();

            if ($ligne) {
                $ligne->update([
                    'numero_ticket' => 'TCK-' . Str::upper(Str::random(8)),
                    'date_impression' => now(),
                    'agent_securite_id' => $agent->id,
                    'statut' => 'imprime',
                ]);
                $idsImprimes[] = $ligne->id;
            }
        }

        if (count($idsImprimes) === 0) {
            return back()->with('error', 'Aucun ticket a imprimer.');
        }

        $tickets = LigneMenu::with('plat', 'collaborateur')
            ->whereIn('id', $idsImprimes)
            ->orderBy('date_repas')
            ->get();

        if ($validated['format'] === 'thermique') {
            return view('agent-securite.tickets-thermique', compact('tickets'));
        }

        return view('agent-securite.tickets-a4', compact('tickets'));
    }
    public function verifierForm(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $recherche = $request->input('recherche');

        $base = LigneMenu::whereIn('statut', ['imprime', 'consomme'])
            ->whereDate('date_repas', $date);

        $attendus = (clone $base)->where('statut', 'imprime')->count();
        $passes = (clone $base)->where('statut', 'consomme')->count();

        $tickets = (clone $base)
            ->with('plat', 'collaborateur')
            ->when($recherche, function ($query) use ($recherche) {
                $query->where(function ($q) use ($recherche) {
                    $q->where('numero_ticket', 'like', "%{$recherche}%")
                        ->orWhereHas('collaborateur', function ($q2) use ($recherche) {
                            $q2->where('matricule', 'like', "%{$recherche}%")
                                ->orWhere('nom', 'like', "%{$recherche}%")
                                ->orWhere('prenom', 'like', "%{$recherche}%");
                        });
                });
            })
            ->orderByRaw("statut = 'consomme'")
            ->get();

        return view('agent-securite.verifier', compact('date', 'attendus', 'passes', 'tickets'));
    }

    public function confirmerRetrait(Request $request)
    {
        $validated = $request->validate([
            'ligne_menu_id' => ['required', 'exists:ligne_menus,id'],
        ]);

        $ligne = LigneMenu::with('plat', 'collaborateur')->findOrFail($validated['ligne_menu_id']);

        if ($ligne->statut === 'consomme') {
            return redirect()->route('agent.verifierForm')->with('error', 'Ce ticket a deja ete utilise.');
        }

        if ($ligne->date_repas->toDateString() !== now()->toDateString()) {
            return redirect()->route('agent.verifierForm')->with('error', "Ce ticket n'est pas valable aujourd'hui.");
        }

        if (! $ligne->collaborateur->estActif()) {
            return redirect()->route('agent.verifierForm')->with('error', 'Le collaborateur est inactif (RG03).');
        }

        $ligne->update([
            'date_retrait' => now(),
            'prix' => $ligne->plat->prix,
            'statut' => 'consomme',
        ]);

        return redirect()->route('agent.verifierForm')->with('success', 'Acces autorise. Le collaborateur peut entrer.');
    }

    public function journalPassages(Request $request)
    {
        $date = $request->input('date', now()->toDateString());

        $passages = LigneMenu::with('plat', 'collaborateur', 'agentSecurite')
            ->where('statut', 'consomme')
            ->whereDate('date_retrait', $date)
            ->orderBy('date_retrait', 'desc')
            ->get();

        return view('agent-securite.journal', compact('passages', 'date'));
    }
}