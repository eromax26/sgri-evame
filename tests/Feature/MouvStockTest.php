<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Collaborateur;
use App\Models\Departement;
use App\Models\MouvStock;
use App\Models\Role;
use App\Models\Societe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Module Stock : sorties de stock (utilisation cuisine, perte, peremption, ajustement),
 * RG11 et historique des mouvements dans l'onglet Article.
 */
class MouvStockTest extends TestCase
{
    use RefreshDatabase;

    private Collaborateur $responsable;

    private Article $riz;

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

        $this->riz = Article::create([
            'libelle' => 'Riz',
            'unite_mesure' => 'kg',
            'quantite_stock' => 10,
            'seuil_minimum' => 5,
        ]);
    }

    private function sortie(array $donnees = [])
    {
        return $this->actingAs($this->responsable)->post(route('mouv-stocks.store'), array_merge([
            'article_id' => $this->riz->id,
            'quantite' => 4,
            'date_mouvement' => '2026-09-28',
            'motif_sortie' => 'Utilisation cuisine',
        ], $donnees));
    }

    public function test_une_sortie_valide_decremente_le_stock_et_cree_un_mouvement(): void
    {
        $this->sortie()->assertRedirect(route('mouv-stocks.index'));

        $this->assertEquals(6.0, (float) $this->riz->fresh()->quantite_stock);
        $this->assertDatabaseHas('mouv_stocks', [
            'article_id' => $this->riz->id,
            'type_mouvement' => 'sortie',
            'quantite' => 4,
            'motif_sortie' => 'Utilisation cuisine',
            'collaborateur_id' => $this->responsable->id,
        ]);
    }

    // RG11 : une sortie est refusee si la quantite depasse le stock disponible
    public function test_une_sortie_superieure_au_stock_est_refusee(): void
    {
        $this->sortie(['quantite' => 11])->assertSessionHas('error');

        $this->assertEquals(10.0, (float) $this->riz->fresh()->quantite_stock);
        $this->assertDatabaseCount('mouv_stocks', 0);
    }

    // RG11 : la limite exacte du stock reste autorisee (stock ramene a zero)
    public function test_une_sortie_egale_au_stock_disponible_est_acceptee(): void
    {
        $this->sortie(['quantite' => 10])->assertSessionHas('success');

        $this->assertEquals(0.0, (float) $this->riz->fresh()->quantite_stock);
        $this->assertTrue($this->riz->fresh()->estEpuise());
        $this->assertDatabaseCount('mouv_stocks', 1);
    }

    public function test_le_motif_de_sortie_est_obligatoire_et_limite_aux_motifs_predefinis(): void
    {
        $this->sortie(['motif_sortie' => ''])->assertSessionHasErrors('motif_sortie');
        $this->sortie(['motif_sortie' => 'Motif inventé'])->assertSessionHasErrors('motif_sortie');

        $this->assertEquals(10.0, (float) $this->riz->fresh()->quantite_stock);
        $this->assertDatabaseCount('mouv_stocks', 0);
    }

    // Les entrees proviennent uniquement des receptions de commandes
    public function test_le_module_stock_n_accepte_pas_de_saisie_d_entree(): void
    {
        $this->sortie(['type_mouvement' => 'entree', 'prix_achat' => 500])->assertRedirect();

        $this->assertEquals(6.0, (float) $this->riz->fresh()->quantite_stock);
        $this->assertDatabaseHas('mouv_stocks', ['type_mouvement' => 'sortie']);
        $this->assertDatabaseMissing('mouv_stocks', ['type_mouvement' => 'entree']);
    }

    public function test_l_historique_des_mouvements_est_visible_dans_l_onglet_article(): void
    {
        $this->sortie(['motif_sortie' => 'Péremption']);

        $this->actingAs($this->responsable)
            ->get(route('articles.show', $this->riz))
            ->assertOk()
            ->assertSee('Riz')
            ->assertSee('Péremption')
            ->assertSee('Saisie manuelle');
    }

    public function test_les_pages_du_module_stock_s_affichent(): void
    {
        $this->sortie();

        $this->actingAs($this->responsable)
            ->get(route('mouv-stocks.index'))
            ->assertOk()
            ->assertSee('Riz');

        $this->actingAs($this->responsable)
            ->get(route('mouv-stocks.create'))
            ->assertOk()
            ->assertSee('Ajustement inventaire');
    }

    public function test_un_collaborateur_sans_le_role_n_accede_pas_aux_mouvements_de_stock(): void
    {
        $societe = Societe::firstOrFail();
        $departement = Departement::where('societe_id', $societe->id)->firstOrFail();

        $sansRole = Collaborateur::create([
            'matricule' => 'MAT-003',
            'nom' => 'KOUAME',
            'prenom' => 'Yao',
            'identifiant' => 'yyao',
            'password' => 'motdepasse',
            'departement_id' => $departement->id,
            'statut' => 'actif',
        ]);

        $this->actingAs($sansRole)->get(route('mouv-stocks.index'))->assertForbidden();
        $this->actingAs($sansRole)->get(route('articles.show', $this->riz))->assertForbidden();
    }

    public function test_les_scopes_et_acceseurs_distinguent_entrees_et_sorties(): void
    {
        MouvStock::create([
            'article_id' => $this->riz->id,
            'collaborateur_id' => $this->responsable->id,
            'type_mouvement' => 'sortie',
            'quantite' => 3,
            'date_mouvement' => '2026-09-28',
            'motif_sortie' => 'Perte',
        ]);

        $this->assertSame(1, MouvStock::sorties()->count());
        $this->assertSame(0, MouvStock::entrees()->count());

        $mouvement = MouvStock::firstOrFail();
        $this->assertTrue($mouvement->est_sortie);
        $this->assertFalse($mouvement->est_entree);
        $this->assertFalse($mouvement->est_lie_a_commande);
    }
}
