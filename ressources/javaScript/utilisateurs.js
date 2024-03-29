import {applyAndRegister, reactive, startReactiveDom} from "./reactive.js";

let utilisateursReactifs = [];

let utilisateurs = reactive({
    prenom: "",
    nom: "",
    login: "",
    colonnes: [],
    drapeau: false,


    afficherContenuUtilisateur: function (loginUtilisateur) {
        if (utilisateursReactifs.length > 0) {
            const utilisateur = utilisateursReactifs.find(utilisateur => utilisateur.login === loginUtilisateur);

            if (!utilisateur) {
                return;
            }

            const div = document.querySelector('.contenuUtilisateur');

            setTimeout(
                () => div.style.display = 'flex',
                div.style.top = `${event.clientY}px`,
                div.style.left = `${event.clientX}px`,
                100
            );

            let html = `<h4>${utilisateur.prenom} ${utilisateur.nom}</h4><ul>`;
            for (let colonne of utilisateur.colonnes) {
                html += `<li>${colonne.titreColonne} : ${colonne.nbCartes}</li>`;
            }
            html += '</ul>';

            this.afficherElements(html);
        }
    },

    afficherElements : function(html = null) {
        console.log("aa");
        if (html) {
            return html;
        }
    },


    /**
     * Charge et crée les utilisateurs à partir de la base de données. N'est lancé qu'au chargement initial de la page
     * @returns {Promise<void>} La promesse habituelle
     * @constructor pour créer les utilisateurs en objets réactifs à partir de this
     */
    MAJUtilisateursDepuisBaseDeDonnees: async function () {

        if (this.drapeau) {
            return;
        }

        utilisateursReactifs = [];

        document.querySelector('.waiting').style.display = 'block';


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
                    let nbCartesJson = await nbCartes.json();
                    listeAffectationsColonnes.push(nbCartesJson);
                }
            }

            for (let membre of tousMembres) {
                let utilisateur = {};
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
                utilisateursReactifs.push(utilisateur);
            }

            document.querySelector('.waiting').style.display = 'none';
            this.drapeau = false;
        }
    },


}, "utilisateur");

applyAndRegister({});

startReactiveDom();


