<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Collaborateur;
use App\Models\Commande;
use App\Models\Departement;
use App\Models\Plat;
use App\Models\Role;
use App\Models\Societe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Recherche dynamique : les fragments JSON renvoyes aux listes filtrees
 * doivent contenir exactement les memes lignes que la vue complete.
 */
class RechercheDynamiqueTest extends TestCase
{
    use RefreshDatabase;

    private Collaborateur $responsable;

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

        foreach (['Riz', 'Huile', 'Sucre'] as $libelle) {
            Plat::create(['code_plat' => 'PLAT-' . $libelle, 'libelle' => $libelle, 'prix' => 1000]);
        }

        foreach (['Riz blanc', 'Huile vegetale', 'Sucre en poudre'] as $libelle) {
            Article::create([
                'libelle' => $libelle,
                'unite_mesure' => 'kg',
                'quantite_stock' => 10,
                'seuil_minimum' => 5,
            ]);
        }
    }

    /** L'Administrateur DSII gere les collaborateurs ; le Responsable cantine, le stock. */
    private function administrateur(): Collaborateur
    {
        $roleAdmin = Role::firstOrCreate(['libelle' => 'Administrateur DSII']);

        $admin = Collaborateur::firstOrCreate(
            ['matricule' => 'MAT-100'],
            [
                'nom' => 'ADMIN',
                'prenom' => 'Awa',
                'identifiant' => 'aadmin',
                'password' => 'motdepasse',
                'departement_id' => $this->responsable->departement_id,
                'statut' => 'actif',
            ]
        );

        $admin->roles()->syncWithoutDetaching([$roleAdmin->id]);

        return $admin;
    }

    public function test_le_fragment_plats_filtre_sur_le_libelle(): void
    {
        $reponse = $this->actingAs($this->responsable)
            ->getJson(route('recherche.plats', ['recherche' => 'huile']));

        $reponse->assertOk();

        $donnees = $reponse->json();

        $this->assertStringContainsString('Huile', $donnees['lignes']);
        $this->assertStringNotContainsString('Riz', $donnees['lignes']);
        $this->assertSame(1, $donnees['total']);
    }

    public function test_le_fragment_articles_ignore_le_texte_vide(): void
    {
        $donnees = $this->actingAs($this->responsable)
            ->getJson(route('recherche.articles'))
            ->json();

        $this->assertSame(3, $donnees['total']);
    }

    public function test_le_fragment_articles_combine_recherche_et_pagination(): void
    {
        // On cree assez d'articles pour depasser une page de 15.
        foreach (range(1, 20) as $index) {
            Article::create([
                'libelle' => 'Article ' . $index,
                'unite_mesure' => 'kg',
                'quantite_stock' => 10,
                'seuil_minimum' => 5,
            ]);
        }

        $donnees = $this->actingAs($this->responsable)
            ->getJson(route('recherche.articles', ['page' => 1]))
            ->json();

        $this->assertSame(23, $donnees['total']);

        // La pagination doit pointer vers la page suivante pour que le JS
        // puisse recharger le resultat sans quitter la page.
        $this->assertStringContainsString('page=2', $donnees['pagination']);
    }

    public function test_le_fragment_plats_et_valide_par_le_role(): void
    {
        $inactif = Plat::where('libelle', 'Sucre')->firstOrFail();
        $inactif->update(['statut' => 'inactif']);

        $donnees = $this->actingAs($this->responsable)
            ->getJson(route('recherche.plats', ['statut' => 'inactif']))
            ->json();

        $this->assertSame(1, $donnees['total']);
        $this->assertStringContainsString('Sucre', $donnees['lignes']);
    }

    public function test_un_collaborateur_sans_le_role_est_refuse(): void
    {
        $roleCollab = Role::create(['libelle' => 'Collaborateur']);
        $simple = Collaborateur::create([
            'matricule' => 'MAT-099',
            'nom' => 'SIMPLE',
            'prenom' => 'Jean',
            'identifiant' => 'jsimple',
            'password' => 'motdepasse',
            'departement_id' => $this->responsable->departement_id,
            'statut' => 'actif',
        ]);
        $simple->roles()->attach($roleCollab->id);

        $this->actingAs($simple)
            ->getJson(route('recherche.plats'))
            ->assertForbidden();
    }

    /**
     * Regression : l'endpoint de recherche doit etre accessible par le meme role
     * que la page qui y renvoie. Un Administrateur voit la liste des collaborateurs
     * mais ne doit pas obtenir un 403 enonant sur son formulaire.
     */
    public function test_l_endpoint_de_recherche_partage_le_role_de_sa_page(): void
    {
        $admin = $this->administrateur();

        $couples = [
            ['collaborateurs.index', 'recherche.collaborateurs'],
            ['plats.index', 'recherche.plats'],
            ['articles.index', 'recherche.articles'],
            ['commandes.index', 'recherche.commandes'],
        ];

        foreach ($couples as [$page, $endpoint]) {
            foreach ([$this->responsable, $admin] as $utilisateur) {
                // Si la page est visible, l'endpoint doit l'etre aussi
                $accesPage = $this->actingAs($utilisateur)->get(route($page));

                $this->assertSame(
                    $accesPage->getStatusCode(),
                    $this->actingAs($utilisateur)->get(route($endpoint))->getStatusCode(),
                    "Page {$page} et endpoint {$endpoint} doivent etre accessibles par le meme role"
                );
            }
        }
    }

    public function test_chaque_liste_filtree_expose_les_hooks_javascript(): void
    {
        $admin = $this->administrateur();

        // Sans JavaScript, le formulaire doit rester utilisable : il pointe vers
        // l'endpoint JSON et le tableau porte la cible de remplacement.
        $pages = [
            'plats.index' => ['recherche.plats', $this->responsable],
            'articles.index' => ['recherche.articles', $this->responsable],
            'commandes.index' => ['recherche.commandes', $this->responsable],
            'collaborateurs.index' => ['recherche.collaborateurs', $admin],
        ];

        foreach ($pages as $vue => [$endpoint, $utilisateur]) {
            $html = $this->actingAs($utilisateur)
                ->get(route($vue))
                ->assertOk()
                ->getContent();

            $this->assertStringContainsString('data-sg-live-search', $html, $vue);
            $this->assertStringContainsString('data-sg-live-target', $html, $vue);
            $this->assertStringContainsString('data-sg-live-pagination', $html, $vue);
            $this->assertStringContainsString(route($endpoint), $html, $vue);
            $this->assertStringContainsString('js/live-search.js', $html, $vue);
        }
    }

    public function test_le_fragment_collaborateurs_filtre_sur_le_nom_et_le_departement(): void
    {
        $admin = $this->administrateur();

        $autre = Departement::create([
            'societe_id' => $this->responsable->departement->societe_id,
            'nom' => 'Production',
        ]);

        Collaborateur::create([
            'matricule' => 'MAT-002',
            'nom' => 'DIALLO',
            'prenom' => 'Moussa',
            'identifiant' => 'mdiallo',
            'password' => 'motdepasse',
            'departement_id' => $autre->id,
            'statut' => 'inactif',
        ]);

        // La liste des collaborateurs est reservee a l'Administrateur DSII
        $parNom = $this->actingAs($admin)
            ->getJson(route('recherche.collaborateurs', ['recherche' => 'DIALLO']))
            ->json();

        $this->assertSame(1, $parNom['total']);
        $this->assertStringContainsString('DIALLO', $parNom['lignes']);

        // Le filtre departement se combine avec la recherche
        $parDepartement = $this->actingAs($admin)
            ->getJson(route('recherche.collaborateurs', ['departement_id' => $autre->id]))
            ->json();

        $this->assertSame(1, $parDepartement['total']);
        $this->assertStringContainsString('DIALLO', $parDepartement['lignes']);
        $this->assertStringNotContainsString('ADJO', $parDepartement['lignes']);
    }

    public function test_le_fragment_commandes_filtre_sur_le_statut(): void
    {
        $commande = Commande::create([
            'collaborateur_id' => $this->responsable->id,
            'date_commande' => now()->toDateString(),
            'statut' => 'brouillon',
        ]);

        $donnees = $this->actingAs($this->responsable)
            ->getJson(route('recherche.commandes', ['statut' => 'brouillon']))
            ->json();

        $this->assertSame(1, $donnees['total']);
        $this->assertStringContainsString('#' . $commande->id, $donnees['lignes']);
    }
}