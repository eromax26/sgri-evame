<?php

namespace App\Http\Controllers;

use App\Models\SelectionRepas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentSecuriteController extends Controller
{
    // L'agent ne fait qu'un seul geste : valider le passage. La consultation des
    // demandes et l'impression en lot ont ete supprimees : le ticket est emis des
    // la selection du repas, et son impression reste facultative.

    public function verifierForm(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $recherche = $request->input('recherche');

        $base = SelectionRepas::whereIn('statut', ['imprime', 'consomme'])
            ->whereHas('ligneMenu', fn ($q) => $q->whereDate('date_repas', $date));

        $attendus = (clone $base)->where('statut', 'imprime')->count();
        $passes = (clone $base)->where('statut', 'consomme')->count();

        $tickets = (clone $base)
            ->with('ligneMenu.plat', 'collaborateur')
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
            'selection_id' => ['required', 'exists:selections_repas,id'],
        ]);

        $selection = SelectionRepas::with('ligneMenu.plat', 'collaborateur')->findOrFail($validated['selection_id']);

        if ($selection->statut === 'consomme') {
            return redirect()->route('agent.verifierForm')->with('error', 'Ce ticket a déjà été utilisé.');
        }

        if ($selection->ligneMenu->date_repas->toDateString() !== now()->toDateString()) {
            return redirect()->route('agent.verifierForm')->with('error', "Ce ticket n'est pas valable aujourd'hui.");
        }

        if (! $selection->collaborateur->estActif()) {
            return redirect()->route('agent.verifierForm')->with('error', 'Le collaborateur est inactif.');
        }

        $selection->update([
            'date_retrait'       => now(),
            'prix'               => $selection->ligneMenu->plat->prix,
            'statut'             => 'consomme',
            'agent_securite_id'  => Auth::id(),
        ]);

        return redirect()->route('agent.verifierForm')->with('success', 'Acces autorisé. Le collaborateur peut entrer.');
    }

    public function journalPassages(Request $request)
    {
        $date = $request->input('date', now()->toDateString());

        $passages = SelectionRepas::with('ligneMenu.plat', 'collaborateur', 'agentSecurite')
            ->where('statut', 'consomme')
            ->whereDate('date_retrait', $date)
            ->orderBy('date_retrait', 'desc')
            ->get();

        return view('agent-securite.journal', compact('passages', 'date'));
    }
}