import {objectByName, applyAndRegister, reactive, startReactiveDom} from "./reactive.js";

let utilisateursReactifs = [];

let utilisateurs = reactive({
    prenom: "",
    nom: "",
    login: "",
    colonnes: [],
    drapeau: false,

    /**
     * Supprime une carte d'une colonne d'un utilisateur
     * @param idCarte l'id de la carte en question
     * @param idUtilisateur l'id de l'utilisateur correspondant
     * @param idColonne l'id de la colonne correspondante
     */
    supprimerCarteUtilisateur: function (idCarte, idUtilisateur, idColonne) {
        const utilisateur = objectByName.get(idUtilisateur);
        let carte = document.querySelector(`[data-carte="${idCarte}"]`);
        let colonne = document.querySelector(`[data-columns="${idColonne}"] .draggable`);
        let colonneUtilisateur = utilisateur.colonnes.find(colonne => colonne.idColonne === idColonne);
        if (colonneUtilisateur) {
            colonneUtilisateur.nbCartes--;
            if (colonneUtilisateur.nbCartes === 0) {
                utilisateur.colonnes = utilisateur.colonnes.filter(colonne => colonne.idColonne !== idColonne);
            }
        }
    },

    /**
     * Ajoute une carte à une colonne d'un utilisateur
     * @param idCarte l'id de la carte
     * @param idUtilisateur l'id de l'utilisateur
     * @param idColonne l'id de la colonne
     */
    ajouterCarteUtilisateur: function (idCarte, idUtilisateur, idColonne) {
        const utilisateur = objectByName.get(idUtilisateur);
        let carte = document.querySelector(`[data-carte="${idCarte}"]`);
        let colonne = document.querySelector(`[data-columns="${idColonne}"]`);
        let colonneUtilisateur = utilisateur.colonnes.find(colonne=> colonne.idColonne === idColonne);
        if (colonneUtilisateur) {
            colonneUtilisateur.nbCartes++;
        } else {
            utilisateur.colonnes.push({
                idColonne: idColonne,
                titreColonne: colonne.querySelector('.main').innerText,
                nbCartes: 1
            });
        }
    },

    /**
     * Affiche les informations d'un utilisateur
     * @param loginUtilisateur le login de l'utilisateur
     */
    afficherContenuUtilisateur: function (loginUtilisateur) {
        if (utilisateursReactifs.length > 0) {
            const utilisateur = objectByName.get(loginUtilisateur);

            if (!utilisateur) {
                return;
            }

            const div = document.querySelector('.' + loginUtilisateur);
            div.setAttribute('data-htmlfun', `utilisateur.afficherElements(${loginUtilisateur})`);
            startReactiveDom();

            div.style.display = 'flex';
            div.style.top = `${event.clientY}px`;
            div.style.left = `((${event.clientX})-50)px`;
        }
    },

    /**
     * Cache le contenu d'un utilisateur
     * @param loginUtilisateur le login de l'utilisateur
     */
    cacherContenuUtilisateur: function (loginUtilisateur) {
        const div = document.querySelector('.' + loginUtilisateur);
        div.style.display = 'none';
    },

    /**
     * Rempli le htmlfun d'une div spécifique avec les informations de l'utilisateur concerné
     * @param idUtilisateur l'id de l'utilisateur
     * @returns {string} le contenu HTML de la div à afficher
     */
    afficherElements: function (idUtilisateur) {
        const utilisateur = utilisateursReactifs.find(utilisateur => utilisateur.login === idUtilisateur);
        let html = `<h4>${utilisateur.prenom} ${utilisateur.nom}</h4><ul>`;
        for (let colonne of utilisateur.colonnes) {
            html += `<li>${colonne.titreColonne} : ${colonne.nbCartes}</li>`;
        }
        html += '</ul>';
        return html;
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

                utilisateur = reactive(utilisateur, membre.login);
                utilisateursReactifs.push(utilisateur);
            }

            document.querySelector('.waiting').style.display = 'none';
            this.drapeau = false;
        }
    },

}, "utilisateur");


window.majUtilisateurs = function () {
    document.querySelector('.waiting').setAttribute('data-onload', 'utilisateur.MAJUtilisateursDepuisBaseDeDonnees()');
    startReactiveDom();
};

window.supprimerCarteUtilisateur = function (idCarte, idUtilisateur, idColonne) {
    utilisateurs.supprimerCarteUtilisateur(idCarte, idUtilisateur, idColonne);
};

window.ajouterCarteUtilisateur = function (idCarte, idUtilisateur, idColonne) {
    utilisateurs.ajouterCarteUtilisateur(idCarte, idUtilisateur, idColonne);
};

applyAndRegister({});

startReactiveDom();


