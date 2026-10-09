<?php

namespace Tests\Feature;

use App\Models\Collaborateur;
use App\Models\Departement;
use App\Models\LigneMenu;
use App\Models\Menu;
use App\Models\Plat;
use App\Models\Role;
use App\Models\SelectionRepas;
use App\Models\Societe;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cycle de vie du ticket de repas.
 *
 * Le ticket est emis des la selection du repas : l'impression est facultative
 * et l'agent de securite se limite a la validation du passage.
 */
class TicketTest extends TestCase
{
    use RefreshDatabase;

    private Collaborateur $collaborateur;

    private Collaborateur $agent;

    private LigneMenu $ligne;

    protected function setUp(): void
    {
        parent::setUp();

        $societe = Societe::create(['nom' => 'EVAME', 'sigle' => 'EV']);
        $departement = Departement::create(['societe_id' => $societe->id, 'nom' => 'Cantine']);

        $roleCollab = Role::create(['libelle' => 'Collaborateur']);
        $roleAgent = Role::create(['libelle' => 'Agent de securite']);

        $this->collaborateur = Collaborateur::create([
            'matricule' => 'MAT-001',
            'nom' => 'ADJO',
            'prenom' => 'Solange',
            'identifiant' => 'sadjo',
            'password' => 'motdepasse',
            'departement_id' => $departement->id,
            'statut' => 'actif',
        ]);
        $this->collaborateur->roles()->attach($roleCollab->id);

        $this->agent = Collaborateur::create([
            'matricule' => 'MAT-002',
            'nom' => 'DIALLO',
            'prenom' => 'Moussa',
            'identifiant' => 'mdiallo',
            'password' => 'motdepasse',
            'departement_id' => $departement->id,
            'statut' => 'actif',
        ]);
        $this->agent->roles()->attach($roleAgent->id);

        $plat = Plat::create(['code_plat' => 'PLAT-001', 'libelle' => 'Riz gras', 'prix' => 1500]);

        // Le repas est place aujourd'hui : la validation de passage exige que le
        // ticket soit valable le jour meme.
        $jourRepas = now();

        $debutSemaine = $jourRepas->copy()->startOfWeek(Carbon::MONDAY);

        $menu = Menu::create([
            'date_debut_semaine' => $debutSemaine->toDateString(),
            'date_fin_semaine' => $debutSemaine->copy()->addDays(4)->toDateString(),
            'statut_publication' => 'publie',
        ]);

        $this->ligne = LigneMenu::create([
            'menu_id' => $menu->id,
            'plat_id' => $plat->id,
            'date_repas' => $jourRepas->toDateString(),
        ]);
    }
    /** Selectionner un repas emet immediatement un ticket valable. */
    public function test_la_selection_emet_immediatement_un_ticket_valable(): void
    {
        $this->actingAs($this->collaborateur)
            ->post(route('selection.valider'), ['lignes' => [$this->ligne->id]])
            ->assertRedirect(route('selection.index'));

        $ticket = SelectionRepas::firstOrFail();

        // Le numero existe des la selection : aucune impression prealable.
        $this->assertNotNull($ticket->numero_ticket);
        $this->assertStringStartsWith('TCK-', $ticket->numero_ticket);

        // Et le ticket est deja valable par l'agent.
        $this->assertSame('imprime', $ticket->statut);
    }

    /** L'impression est facultative et ne modifie plus le numero ni le statut. */
    public function test_imprimer_un_ticket_est_facultatif_et_idempotent(): void
    {
        $this->actingAs($this->collaborateur)
            ->post(route('selection.valider'), ['lignes' => [$this->ligne->id]]);

        $avant = SelectionRepas::firstOrFail();

        $this->actingAs($this->collaborateur)
            ->get(route('selection.imprimerTicket', $avant))
            ->assertOk();

        $apres = $avant->fresh();

        $this->assertSame($avant->numero_ticket, $apres->numero_ticket);
        $this->assertSame('imprime', $apres->statut);
        $this->assertNotNull($apres->date_impression);
    }

    /** L'agent valide un ticket jamais imprime. */
    public function test_l_agent_valide_un_ticket_jamais_imprime(): void
    {
        $this->actingAs($this->collaborateur)
            ->post(route('selection.valider'), ['lignes' => [$this->ligne->id]]);

        $ticket = SelectionRepas::firstOrFail();
        $this->assertNull($ticket->date_impression);

        $this->actingAs($this->agent)
            ->get(route('agent.verifierForm'))
            ->assertOk()
            ->assertSee($ticket->numero_ticket);

        $this->actingAs($this->agent)
            ->post(route('agent.confirmerRetrait'), ['selection_id' => $ticket->id])
            ->assertRedirect(route('agent.verifierForm'));

        $ticket->refresh();

        $this->assertSame('consomme', $ticket->statut);
        $this->assertNotNull($ticket->date_retrait);
        $this->assertEquals(1500.0, (float) $ticket->prix);
    }
    /** L'agent n'a plus de page de consultation des demandes. */
    public function test_l_agent_na_plus_acces_aux_demandes_de_tickets(): void
    {
        $this->actingAs($this->agent)
            ->get('/agent-securite/demandes')
            ->assertNotFound();
    }

    /** Le collaborateur ne peut pas imprimer le ticket d'un autre. */
    public function test_un_collaborateur_ne_peut_pas_imprimer_le_ticket_d_autrui(): void
    {
        $this->actingAs($this->collaborateur)
            ->post(route('selection.valider'), ['lignes' => [$this->ligne->id]]);

        $ticket = SelectionRepas::firstOrFail();

        $this->actingAs($this->agent)
            ->get(route('selection.imprimerTicket', $ticket))
            ->assertForbidden();
    }

    /** Un ticket deja consomme ne peut pas servir deux fois. */
    public function test_un_ticket_deja_consomme_est_refuse(): void
    {
        $this->actingAs($this->collaborateur)
            ->post(route('selection.valider'), ['lignes' => [$this->ligne->id]]);

        $ticket = SelectionRepas::firstOrFail();

        $this->actingAs($this->agent)
            ->post(route('agent.confirmerRetrait'), ['selection_id' => $ticket->id]);

        $this->actingAs($this->agent)
            ->post(route('agent.confirmerRetrait'), ['selection_id' => $ticket->id])
            ->assertSessionHas('error');

        $this->assertSame('consomme', $ticket->fresh()->statut);
    }

    /** Un ticket d'un jour deja passe ne peut pas non plus etre valide. */
    public function test_un_ticket_d_un_jour_deja_passe_ne_peut_pas_valider_le_passage(): void
    {
        $ligneHier = LigneMenu::create([
            'menu_id' => $this->ligne->menu_id,
            'plat_id' => $this->ligne->plat_id,
            'date_repas' => now()->subDay()->toDateString(),
        ]);

        // La selection refuse par elle-meme les repas passes : on cree la ligne
        // directement pour verifier le garde-fou de l'agent, seul rempart possible.
        $ticketHier = SelectionRepas::create([
            'ligne_menu_id' => $ligneHier->id,
            'collaborateur_id' => $this->collaborateur->id,
            'date_selection' => now(),
            'numero_ticket' => 'TCK-HIER0001',
            'statut' => 'imprime',
        ]);

        $this->actingAs($this->agent)
            ->post(route('agent.confirmerRetrait'), ['selection_id' => $ticketHier->id])
            ->assertSessionHas('error');

        $this->assertSame('imprime', $ticketHier->fresh()->statut);
        $this->assertNull($ticketHier->fresh()->date_retrait);
    }

    /**
     * Un ticket de demain (lundi selectionne, mardi en attente) ne doit pas
     * pouvoir etre valide alors que nous sommes lundi : changer la date du
     * formulaire ne doit pas autoriser un passage anticipe.
     */
    public function test_un_ticket_d_un_jour_a_venir_ne_peut_pas_valider_le_passage(): void
    {
        $ligneDemain = LigneMenu::create([
            'menu_id' => $this->ligne->menu_id,
            'plat_id' => $this->ligne->plat_id,
            'date_repas' => now()->addDay()->toDateString(),
        ]);

        $this->actingAs($this->collaborateur)
            ->post(route('selection.valider'), ['lignes' => [$this->ligne->id, $ligneDemain->id]]);

        $ticketDemain = SelectionRepas::where('ligne_menu_id', $ligneDemain->id)->firstOrFail();

        // Le ticket est bien emis et visible dans le tableau de verification du lendemain
        $this->actingAs($this->agent)
            ->get(route('agent.verifierForm', ['date' => now()->addDay()->toDateString()]))
            ->assertOk()
            ->assertSee($ticketDemain->numero_ticket);

        // ...mais la validation du passage est refusee : le repas n'est pas aujourd'hui
        $this->actingAs($this->agent)
            ->post(route('agent.confirmerRetrait'), ['selection_id' => $ticketDemain->id])
            ->assertSessionHas('error');

        $this->assertSame('imprime', $ticketDemain->fresh()->statut);
        $this->assertNull($ticketDemain->fresh()->date_retrait);
        $this->assertNull($ticketDemain->fresh()->prix);
    }
}