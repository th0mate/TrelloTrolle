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
        let colonneUtilisateur = utilisateur.colonnes.find(colonne => colonne.idColonne === idColonne);
        if (colonneUtilisateur) {
            colonneUtilisateur.nbCartes++;
        } else {
            utilisateur.colonnes.push({
                idColonne: idColonne,
                titreColonne: escapeHtml(colonne.querySelector('.main').innerText),
                nbCartes: 1
            });
        }
    },


    /**
     * Supprime un utilisateur d'un tableau
     * @param idUtilisateur l'id de l'utilisateur à supprimer
     * @returns {Promise<void>} La promesse habituelle
     */
    supprimerUtilisateur: async function (idUtilisateur) {
        utilisateursReactifs = utilisateursReactifs.filter(utilisateur => utilisateur.login !== idUtilisateur);
        document.querySelectorAll(`[data-user="${idUtilisateur}"]`).forEach(element => element.remove());

        let response = await fetch(apiBase + '/tableau/membre/supprimer', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                idTableau: document.querySelector('.adder').getAttribute('data-tableau'),
                login: idUtilisateur
            })
        });

        if (response.status !== 200) {
            afficherMessageFlash("Erreur lors de la suppression de l'utilisateur du tableau", "danger")
        } else {
            window.majUtilisateursListeParticipants();
            afficherMessageFlash("Utilisateur supprimé du tableau avec succès", "success")
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
     * Affiche la div de suppression d'un utilisateur
     * @param loginUtilisateur le login de l'utilisateur
     */
    afficherSupprimer: function (loginUtilisateur) {
        const div = document.querySelector('.divSupprimerUtilisateur');
        div.style.display = 'flex';
        div.style.top = `${event.clientY}px`;
        div.style.left = `((${event.clientX})-50)px`;
        document.querySelector('body').addEventListener('click', () => div.style.display = 'none');
        div.querySelector('span').setAttribute('data-onclick', `utilisateur.supprimerUtilisateur(${loginUtilisateur})`);
        startReactiveDom();
    },


    /**
     * Rempli le htmlfun d'une div spécifique avec les informations de l'utilisateur concerné
     * @param idUtilisateur l'id de l'utilisateur
     * @returns {string} le contenu HTML de la div à afficher
     */
    afficherElements: function (idUtilisateur) {
        const utilisateur = utilisateursReactifs.find(utilisateur => utilisateur.login === idUtilisateur);
        let html = `<h4>${escapeHtml(utilisateur.prenom)} ${escapeHtml(utilisateur.nom)}</h4><ul>`;
        for (let colonne of utilisateur.colonnes) {
            html += `<li>${escapeHtml(colonne.titreColonne)} : ${colonne.nbCartes}</li>`;
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
            afficherMessageFlash("Erreur lors de la récupération des membres du tableau", "danger");

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
                    afficherMessageFlash("Erreur lors de la récupération des affectations de colonnes", "danger");
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

                if (!document.querySelector(`[data-user="${membre.login}"]`)) {
                    if (estProprio) {
                        document.querySelector('.invite').remove();
                    }
                    let html = `<span class="user" data-onhover="utilisateur.afficherContenuUtilisateur(${membre.login})" data-onrightclick="utilisateur.afficherSupprimer(${membre.login})" data-onleave="utilisateur.cacherContenuUtilisateur(${membre.login})" data-user="${membre.login}">${membre.prenom[0].toUpperCase()}${membre.nom[0].toUpperCase()}</span><div class="contenuUtilisateur ${membre.login}"></div>`;
                    document.querySelector('.allUsers').innerHTML += html;

                    if (estProprio) {
                        document.querySelector('.allUsers').innerHTML += `<div class="invite">Partager <img src="${inviterImageUrl}" alt=""></div>`;
                    }

                    randomColorsPourUsersDifferents();
                    startReactiveDom();
                }

                for (let affectation of listeAffectationsColonnes) {
                    if (affectation[membre.login] !== undefined) {
                        for (let [idColonne, [titreColonne, nbCartes]] of Object.entries(affectation[membre.login].colonnes)) {
                            utilisateur.colonnes.push({
                                idColonne: idColonne,
                                titreColonne: escapeHtml(titreColonne),
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


async function recupererUtilisateursDepuisLogin(logins) {
    for (let login of logins) {
        let response = await fetch(apiBase + '/utilisateur/get', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                login: login
            })
        });

        if (response.status !== 200) {
            afficherMessageFlash("Erreur lors de la récupération des utilisateurs", "danger");
        } else {
            let utilisateur = await response.json();
            let utilisateurReac = reactive({
                prenom: utilisateur.prenom,
                nom: utilisateur.nom,
                login: utilisateur.login,
                colonnes: [],
                drapeau: false
            }, utilisateur.login);
            utilisateursReactifs.push(utilisateurReac);
        }
    }
}

applyAndRegister({});

startReactiveDom();


