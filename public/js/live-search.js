/**
 * Recherche dynamique (filtrage a la frappe) pour les listes de l'application.
 *
 * Principe : le serveur reste seul moteur du rendu. On recharge la page avec les
 * criteres en query string, puis on remplace le <tbody> et la pagination par
 * ceux recus en reponse. Sans JavaScript, le formulaire classique prend le relais :
 * le filtrage fonctionne dans les deux cas.
 *
 * Usage dans une vue :
 *   <form data-sg-live-search>
 *       <input name="recherche">
 *       <table data-sg-live-target> ... </table>
 *   </form>
 *   <div data-sg-live-pagination> {{ $x->links() }} </div>
 */
(function () {
    'use strict';

    var DELAI_SAISIE = 300; // ms avant d'envoyer la requete

    function valeurFormulaire(form) {
        var donnees = new FormData(form);
        var params = new URLSearchParams();

        donnees.forEach(function (valeur, cle) {
            // Les champs vides sont omis : l'URL reste lisible et le serveur
            // applique exactement les memes criteres qu'un GET classique.
            if (valeur !== '' && valeur !== null && valeur !== undefined) {
                params.append(cle, valeur);
            }
        });

        return params;
    }

    function initialiser(form) {
        var cible = document.querySelector('[data-sg-live-target]');
        var zonePagination = document.querySelector('[data-sg-live-pagination]');

        if (!cible) {
            return;
        }

        var minuterie = null;
        var derniereRequete = 0;

        function afficher(bodyHtml, paginationHtml) {
            cible.innerHTML = bodyHtml;

            if (zonePagination && paginationHtml !== null && paginationHtml !== undefined) {
                zonePagination.innerHTML = paginationHtml;
            }
        }

        function recharger() {
            var params = valeurFormulaire(form);
            var url = form.getAttribute('action') + '?' + params.toString();

            // Numero de sequence : si l'utilisateur tape vite, seule la reponse la
            // plus recente est affichee, evitant qu'un resultat plus ancien arrive apres.
            var sequence = ++derniereRequete;
            var urlReelle = url;

            fetch(urlReelle, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(function (reponse) {
                    if (!reponse.ok) {
                        throw new Error('HTTP ' + reponse.status);
                    }
                    return reponse.json();
                })
                .then(function (data) {
                    if (sequence !== derniereRequete) {
                        return; // une requete plus recente est en cours
                    }

                    afficher(data.lignes, data.pagination);

                    // L'URL du navigateur suit le filtre : le bouton "retour"
                    // ramene a l'etat precedent et la page reste partageable.
                    if (window.history && window.history.replaceState) {
                        window.history.replaceState({}, '', urlReelle);
                    }
                })
                .catch(function () {
                    // Reseau coupe ou erreur serveur : on laisse la page telle
                    // quelle plutot que d'afficher un etat incoherent.
                });
        }

        function planifier() {
            window.clearTimeout(minuterie);
            minuterie = window.setTimeout(recharger, DELAI_SAISIE);
        }

        // 'input' pour les champs texte, 'change' pour les listes et dates :
        // on couvre les deux natures de champs avec le meme delegate.
        form.addEventListener('input', planifier);
        form.addEventListener('change', planifier);

        // La pagination recharge elle aussi en AJAX. On intercepte le clic sur
        // les liens produits par Laravel pour ne pas quitter la page.
        var conteneurPagination = zonePagination || document;

        conteneurPagination.addEventListener('click', function (evenement) {
            var lien = evenement.target.closest('a');

            if (!lien || !lien.href || !lien.href.includes('?page=')) {
                return;
            }

            evenement.preventDefault();

            // On repart des criteres courants, en changeant juste la page.
            var params = valeurFormulaire(form);
            params.set('page', new URL(lien.href).searchParams.get('page'));

            fetch(form.getAttribute('action') + '?' + params.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    afficher(data.lignes, data.pagination);

                    if (window.history && window.history.replaceState) {
                        window.history.replaceState(
                            {},
                            '',
                            form.getAttribute('action') + '?' + params.toString()
                        );
                    }
                })
                .catch(function () {});
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form[data-sg-live-search]').forEach(initialiser);
    });
})();