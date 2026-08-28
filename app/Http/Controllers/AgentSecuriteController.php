<?php

namespace App\Http\Controllers;

use App\Models\SelectionRepas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AgentSecuriteController extends Controller
{
    public function demandesEnAttente()
    {
        $demandes = SelectionRepas::with('ligneMenu.plat', 'collaborateur')
            ->where('statut', 'demande')
            ->get()
            ->sortBy(fn ($selection) => $selection->ligneMenu->date_repas)
            ->values();

        return view('agent-securite.demandes', compact('demandes'));
    }

    public function imprimer(Request $request)
    {
        $validated = $request->validate([
            'lignes' => ['required', 'array', 'min:1'],
            'lignes.*' => ['exists:selections_repas,id'],
            'format' => ['required', 'in:thermique,a4'],
        ]);

        $agent = Auth::user();
        $idsImprimes = [];

        foreach ($validated['lignes'] as $selectionId) {
            $selection = SelectionRepas::where('id', $selectionId)
                ->where('statut', 'demande')
                ->first();

            if ($selection) {
                $selection->update([
                    'numero_ticket' => 'TCK-' . Str::upper(Str::random(8)),
                    'date_impression' => now(),
                    'agent_securite_id' => $agent->id,
                    'statut' => 'imprime',
                ]);
                $idsImprimes[] = $selection->id;
            }
        }

        if (count($idsImprimes) === 0) {
            return back()->with('error', 'Aucun ticket a imprimer.');
        }

        $tickets = SelectionRepas::with('ligneMenu.plat', 'collaborateur')
            ->whereIn('id', $idsImprimes)
            ->get()
            ->sortBy(fn ($selection) => $selection->ligneMenu->date_repas)
            ->values();

        if ($validated['format'] === 'thermique') {
            return view('agent-securite.tickets-thermique', compact('tickets'));
        }

        return view('agent-securite.tickets-a4', compact('tickets'));
    }
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
            return redirect()->route('agent.verifierForm')->with('error', 'Ce ticket a deja ete utilise.');
        }

        if ($selection->ligneMenu->date_repas->toDateString() !== now()->toDateString()) {
            return redirect()->route('agent.verifierForm')->with('error', "Ce ticket n'est pas valable aujourd'hui.");
        }

        if (! $selection->collaborateur->estActif()) {
            return redirect()->route('agent.verifierForm')->with('error', 'Le collaborateur est inactif (RG03).');
        }

        $selection->update([
            'date_retrait' => now(),
            'prix' => $selection->ligneMenu->plat->prix,
            'statut' => 'consomme',
        ]);

        return redirect()->route('agent.verifierForm')->with('success', 'Acces autorise. Le collaborateur peut entrer.');
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