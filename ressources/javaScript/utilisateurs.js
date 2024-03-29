import {applyAndRegister, reactive, startReactiveDom} from "./reactive.js";

let utilisateurs = reactive({
    prenom: "",
    nom: "",
    login: "",
    colonnes: [],
    drapeau: false,

    creerUtilisateurDepuisBaseDeDonnees: async function () {

        if (this.drapeau) {
            return;
        }

        this.drapeau = true;

        let membresTableau = await fetch(apiBase + '/tableau/membre/getPourTableau', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                idTableau: document.querySelector('.adder').getAttribute('data-tableau')
            })
        });

        let proprioTableau = await fetch(apiBase + '/tableau/membre/getProprio', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                idTableau: document.querySelector('.adder').getAttribute('data-tableau')
            })
        });

        if (membresTableau.status !== 200 || proprioTableau.status !== 200) {
            console.error("Erreur lors de la récupération des membres du tableau");

        } else {
            let membres = await membresTableau.json();
            let proprio = await proprioTableau.json();
            const tousMembres = membres.concat(proprio);

            let colonnes = document.querySelectorAll('.draggable');
            const listeAffectationsColonnes = [];

            for (let colonne of colonnes) {
                let nbCartes = await fetch(apiBase + '/utilisateur/affectations', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        idColonne: colonne.getAttribute('data-columns')
                    })
                });

                if (nbCartes.status !== 200) {
                    console.error(nbCartes.json());
                } else {
                    /**
                     * Retourne
                     * "loginUtilisateur": {
                     *         "colonnes": {
                     *             "idColonne": [
                     *                 "titreColonne",
                     *                 nbCartes
                     *             ]
                     *         }
                     *     },
                     */
                    let nbCartesJson = await nbCartes.json();

                    //console.log(JSON.stringify(nbCartesJson, null, 2));
                    listeAffectationsColonnes.push(nbCartesJson);
                }
            }
            /**
             * Contenu de listeAffectationsColonnes :
             * [
             *   {
             *     "Bouah": {
             *       "colonnes": {
             *         "47": [
             *           "iuefhlsd",
             *           1
             *         ]
             *       }
             *     },
             *     "touzer": {
             *       "colonnes": {
             *         "47": [
             *           "iuefhlsd",
             *           1
             *         ]
             *       }
             *     },
             *     "ffb": {
             *       "colonnes": {
             *         "47": [
             *           "iuefhlsd",
             *           1
             *         ]
             *       }
             *     }
             *   },
             *   {
             *     "Bouah": {
             *       "colonnes": {
             *         "49": [
             *           "tests",
             *           1
             *         ]
             *       }
             *     }...
             */

            for (let membre of tousMembres) {
                let utilisateur = Object.create(this);
                utilisateur.prenom = membre.prenom;
                utilisateur.nom = membre.nom;
                utilisateur.login = membre.login;
                utilisateur.colonnes = [];
                utilisateur.drapeau = false;

                for (let affectation of listeAffectationsColonnes) {
                    if (affectation[membre.login] !== undefined) {
                        for (let [idColonne, [titreColonne, nbCartes]] of Object.entries(affectation[membre.login].colonnes)) {
                            utilisateur.colonnes.push({
                                idColonne: idColonne,
                                titreColonne: titreColonne,
                                nbCartes: nbCartes
                            });
                        }
                    }
                }

                console.log(utilisateur);

            }

            this.drapeau = false;
        }
    },


}, "utilisateur");

applyAndRegister({});

startReactiveDom();


