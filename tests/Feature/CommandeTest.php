<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Collaborateur;
use App\Models\Commande;
use App\Models\Departement;
use App\Models\LigneCommande;
use App\Models\MouvStock;
use App\Models\Role;
use App\Models\Societe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Parcours complet du module Commandes : creation d'une liste, validation,
 * receptions (groupee et ligne par ligne), impacts sur le stock et sur les statuts.
 */
class CommandeTest extends TestCase
{
    use RefreshDatabase;

    private Collaborateur $responsable;

    private Collaborateur $autreCollaborateur;

    private Article $riz;

    private Article $huile;

    protected function setUp(): void
    {
        parent::setUp();

        $societe = Societe::create(['nom' => 'EVAME', 'sigle' => 'EV']);
        $departement = Departement::create(['societe_id' => $societe->id, 'nom' => 'Cantine']);
        $role = Role::create(['libelle' => 'Responsable cantine']);

        $this->responsable = Collaborateur::create([
            'matricule' => 'MAT-001',
            'nom' => 'ADJO',
            'prenom' => 'Solange',
            'identifiant' => 'sadjo',
            'password' => 'motdepasse',
            'departement_id' => $departement->id,
            'statut' => 'actif',
        ]);
        $this->responsable->roles()->attach($role->id);

        $this->autreCollaborateur = Collaborateur::create([
            'matricule' => 'MAT-002',
            'nom' => 'DIALLO',
            'prenom' => 'Moussa',
            'identifiant' => 'mdiallo',
            'password' => 'motdepasse',
            'departement_id' => $departement->id,
            'statut' => 'actif',
        ]);

        $this->riz = Article::create([
            'libelle' => 'Riz',
            'unite_mesure' => 'kg',
            'quantite_stock' => 10,
            'seuil_minimum' => 5,
        ]);

        $this->huile = Article::create([
            'libelle' => 'Huile',
            'unite_mesure' => 'L',
            'quantite_stock' => 0,
            'seuil_minimum' => 2,
        ]);
    }

    /** Brouillon puis liste validee, prete a etre receptionnee. */
    private function commandeValidee(): Commande
    {
        $commande = Commande::create([
            'collaborateur_id' => $this->responsable->id,
            'date_commande' => '2026-09-20',
            'statut' => 'brouillon',
        ]);

        LigneCommande::create([
            'commande_id' => $commande->id,
            'article_id' => $this->riz->id,
            'quantite_demandee' => 20,
            'prix_estime_unitaire' => 500,
        ]);

        LigneCommande::create([
            'commande_id' => $commande->id,
            'article_id' => $this->huile->id,
            'quantite_demandee' => 10,
            'prix_estime_unitaire' => 1000,
        ]);

        $commande->valider();

        return $commande->fresh();
    }

    public function test_le_responsable_cree_et_valide_une_commande(): void
    {
        $this->actingAs($this->responsable)
            ->post(route('commandes.store'), ['date_commande' => '2026-09-20', 'commentaire' => 'Liste de septembre'])
            ->assertRedirect();

        $commande = Commande::firstOrFail();
        $this->assertSame('brouillon', $commande->statut);
        $this->assertSame($this->responsable->id, $commande->collaborateur_id);

        $this->actingAs($this->responsable)
            ->post(route('commandes.lignes.store', $commande), [
                'article_id' => $this->riz->id,
                'quantite_demandee' => 20,
                'prix_estime_unitaire' => 500,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('ligne_commandes', [
            'commande_id' => $commande->id,
            'article_id' => $this->riz->id,
        ]);

        // Un meme article ne peut pas figurer deux fois dans la meme liste
        $this->actingAs($this->responsable)
            ->post(route('commandes.lignes.store', $commande), [
                'article_id' => $this->riz->id,
                'quantite_demandee' => 5,
            ])
            ->assertSessionHasErrors('article_id');

        $this->actingAs($this->responsable)
            ->post(route('commandes.valider', $commande))
            ->assertRedirect(route('commandes.show', $commande));

        $commande->refresh();
        $this->assertSame('validee', $commande->statut);
        $this->assertEquals(10000.0, (float) $commande->prix_estime_total);

        // La page d'impression propose la saisie des receptions
        $this->actingAs($this->responsable)
            ->get(route('commandes.show', $commande))
            ->assertOk()
            ->assertSee('Riz')
            // Le tableau detaille doit rester en pleine largeur : la classe
            // .sg-card-form plafonne les cartes a 520px et ecrasait les montants.
            ->assertSee('sg-table-commande', false)
            ->assertDontSee('sg-card-form', false);
    }

    public function test_la_page_de_modification_ne_plafonne_pas_la_largeur_des_tableaux(): void
    {
        $commande = $this->commandeValidee();

        $this->actingAs($this->responsable)
            ->get(route('commandes.edit', $commande))
            ->assertOk()
            ->assertSee('sg-table-commande', false)
            ->assertDontSee('sg-card-form', false);
    }

    public function test_la_reception_groupee_met_a_jour_le_stock_et_le_statut(): void
    {
        $commande = $this->commandeValidee();
        $ligneRiz = $commande->lignes()->where('article_id', $this->riz->id)->firstOrFail();

        $this->actingAs($this->responsable)
            ->post(route('commandes.achats', $commande), [
                'date_achat' => '2026-09-22',
                'lignes' => [
                    $ligneRiz->id => ['quantite' => 8, 'prix' => 550],
                ],
            ])
            ->assertRedirect(route('commandes.show', $commande));

        $commande->refresh();
        $this->assertSame('livree_partielle', $commande->statut);
        $this->assertSame('2026-09-22', $commande->date_achat->toDateString());

        // Stock augmente de la quantite reellement recue
        $this->assertEquals(18.0, (float) $this->riz->fresh()->quantite_stock);
        $this->assertEquals(0.0, (float) $this->huile->fresh()->quantite_stock);

        $ligneRiz->refresh();
        $this->assertEquals(8.0, (float) $ligneRiz->quantite_livree);
        $this->assertEquals(550.0, (float) $ligneRiz->prix_achat_reel);

        // Le mouvement de stock reste rattache a la commande et a sa ligne
        $mouvement = MouvStock::where('commande_id', $commande->id)->firstOrFail();
        $this->assertSame($ligneRiz->id, $mouvement->ligne_commande_id);
        $this->assertSame('entree', $mouvement->type_mouvement);
        $this->assertEquals(8.0, (float) $mouvement->quantite);
        $this->assertEquals(550.0, (float) $mouvement->prix_achat);

        // Le reste a recevoir est visible dans la liste des articles
        $this->assertEquals(12.0, $this->riz->quantiteCommandeeNonLivree());
    }

    public function test_la_reception_totale_marque_la_commande_livree(): void
    {
        $commande = $this->commandeValidee();

        $lignes = [];
        foreach ($commande->lignes as $ligne) {
            $lignes[$ligne->id] = ['quantite' => (float) $ligne->quantite_demandee, 'prix' => 600];
        }

        $this->actingAs($this->responsable)
            ->post(route('commandes.achats', $commande), ['date_achat' => '2026-09-23', 'lignes' => $lignes])
            ->assertRedirect(route('commandes.show', $commande));

        $this->assertSame('livree', $commande->fresh()->statut);
        $this->assertEquals(2, MouvStock::where('commande_id', $commande->id)->count());
        $this->assertEquals(30.0, (float) $this->riz->fresh()->quantite_stock);
        $this->assertEquals(0.0, $this->riz->quantiteCommandeeNonLivree());
    }

    public function test_une_reception_ne_peut_pas_depasser_le_reste_a_recevoir(): void
    {
        $commande = $this->commandeValidee();
        $ligne = $commande->lignes()->where('article_id', $this->riz->id)->firstOrFail();

        $this->actingAs($this->responsable)
            ->post(route('commandes.lignes.livrer', [$commande, $ligne]), [
                'quantite_livree' => 999,
                'prix_achat_reel' => 500,
                'date_livraison' => '2026-09-24',
            ])
            ->assertSessionHasErrors('quantite_livree');

        $this->assertEquals(0.0, (float) $ligne->fresh()->quantite_livree);
        $this->assertEquals(10.0, (float) $this->riz->fresh()->quantite_stock);
        $this->assertDatabaseCount('mouv_stocks', 0);
    }

    public function test_une_reception_partielle_par_ligne_est_acceptee(): void
    {
        $commande = $this->commandeValidee();
        $ligne = $commande->lignes()->where('article_id', $this->riz->id)->firstOrFail();

        $this->actingAs($this->responsable)
            ->post(route('commandes.lignes.livrer', [$commande, $ligne]), [
                'quantite_livree' => 5,
                'prix_achat_reel' => 480,
                'date_livraison' => '2026-09-24',
            ])
            ->assertRedirect();

        $this->assertSame('livree_partielle', $commande->fresh()->statut);
        $this->assertEquals(15.0, (float) $this->riz->fresh()->quantite_stock);
        $this->assertEquals(5.0, (float) $ligne->fresh()->quantite_livree);
        $this->assertEquals(15.0, (float) $ligne->fresh()->reste_a_livrer);
    }

    public function test_une_commande_non_validee_n_accepte_pas_de_reception(): void
    {
        $commande = Commande::create([
            'collaborateur_id' => $this->responsable->id,
            'date_commande' => '2026-09-20',
            'statut' => 'brouillon',
        ]);

        $ligne = LigneCommande::create([
            'commande_id' => $commande->id,
            'article_id' => $this->riz->id,
            'quantite_demandee' => 5,
        ]);

        $this->actingAs($this->responsable)
            ->post(route('commandes.achats', $commande), [
                'date_achat' => '2026-09-25',
                'lignes' => [$ligne->id => ['quantite' => 5, 'prix' => 500]],
            ])
            ->assertSessionHas('error');

        $this->assertSame('brouillon', $commande->fresh()->statut);
        $this->assertDatabaseCount('mouv_stocks', 0);
    }

    public function test_la_suppression_d_un_brouillon_supprime_ses_lignes(): void
    {
        $commande = Commande::create([
            'collaborateur_id' => $this->responsable->id,
            'date_commande' => '2026-09-20',
            'statut' => 'brouillon',
        ]);

        LigneCommande::create([
            'commande_id' => $commande->id,
            'article_id' => $this->riz->id,
            'quantite_demandee' => 5,
        ]);

        $this->actingAs($this->responsable)
            ->delete(route('commandes.destroy', $commande))
            ->assertRedirect(route('commandes.index'));

        $this->assertDatabaseMissing('commandes', ['id' => $commande->id]);
        $this->assertDatabaseMissing('ligne_commandes', ['commande_id' => $commande->id]);
    }

    public function test_une_commande_validee_ne_peut_pas_etre_supprimee(): void
    {
        $commande = $this->commandeValidee();

        $this->actingAs($this->responsable)
            ->delete(route('commandes.destroy', $commande))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('commandes', ['id' => $commande->id]);
    }

    public function test_une_liste_partiellement_livree_peut_etre_cloturee(): void
    {
        $commande = $this->commandeValidee();
        $ligne = $commande->lignes()->where('article_id', $this->riz->id)->firstOrFail();

        // 15 kg achetes sur les 20 demandes : le reste passe en "a recevoir"
        $ligne->livrer(15, 500, '2026-09-24');
        $commande->refresh();

        $this->assertSame('livree_partielle', $commande->statut);
        $this->assertEquals(5.0, $this->riz->fresh()->quantiteCommandeeNonLivree());

        $this->actingAs($this->responsable)
            ->post(route('commandes.cloturer', $commande))
            ->assertRedirect(route('commandes.show', $commande));

        $commande->refresh();

        $this->assertSame('cloturee', $commande->statut);
        $this->assertTrue($commande->est_cloturee);

        // RG16 : le reste n'est plus attendu, l'article ne l'annonce plus
        $this->assertEquals(0.0, $this->riz->fresh()->quantiteCommandeeNonLivree());
        $this->assertEquals(0.0, $this->huile->fresh()->quantiteCommandeeNonLivree());
    }

    public function test_une_liste_cloturee_n_accepte_plus_de_reception(): void
    {
        $commande = $this->commandeValidee();
        $ligne = $commande->lignes()->where('article_id', $this->riz->id)->firstOrFail();

        $ligne->livrer(15, 500, '2026-09-24');

        // La livraison a change le statut en base : on recharge avant de cloturer
        $commande->refresh();
        $this->assertTrue($commande->cloturer());

        $this->assertFalse($ligne->fresh()->peut_etre_livree);

        $this->actingAs($this->responsable)
            ->post(route('commandes.lignes.livrer', [$commande, $ligne]), [
                'quantite_livree' => 5,
                'prix_achat_reel' => 500,
                'date_livraison' => '2026-09-26',
            ])
            ->assertSessionHas('error');

        // Le stock gagne par la premiere reception est conserve
        $this->assertEquals(25.0, (float) $this->riz->fresh()->quantite_stock);
        $this->assertDatabaseCount('mouv_stocks', 1);
    }

    public function test_une_liste_sans_achat_ne_peut_pas_etre_cloturee(): void
    {
        $commande = $this->commandeValidee();

        $this->actingAs($this->responsable)
            ->post(route('commandes.cloturer', $commande))
            ->assertSessionHas('error');

        $this->assertSame('validee', $commande->fresh()->statut);
    }

    public function test_un_collaborateur_sans_le_role_n_accede_pas_aux_commandes(): void
    {
        $this->actingAs($this->autreCollaborateur)
            ->get(route('commandes.index'))
            ->assertForbidden();
    }
}
