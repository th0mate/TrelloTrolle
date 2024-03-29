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


            for (let membre of tousMembres) {
                let utilisateur = Object.create(this);
                utilisateur.prenom = membre.prenom;
                utilisateur.nom = membre.nom;
                utilisateur.login = membre.login;
                utilisateur.colonnes = [];
                utilisateur.drapeau = false;

                console.log(utilisateur);

                let colonnes = document.querySelectorAll('.draggable');

                for (let colonne of colonnes) {
                    let nbCartes = await fetch(apiBase + '/utilisateur/affectations', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            idColonne: colonne.getAttribute('data-colonne')
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

                        console.log(JSON.stringify(nbCartesJson, null, 2));
                    }
                }

            }

            this.drapeau = false;
        }
    },


}, "utilisateur");

applyAndRegister({});

startReactiveDom();


