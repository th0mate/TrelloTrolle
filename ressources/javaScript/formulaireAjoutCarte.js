import {applyAndRegister, objectByName, reactive, startReactiveDom} from "./reactive.js";
/**
 * Objet réactif formulaireAjoutCarte
 * @type {*|Object|boolean} un objet réactif
 */
let formulaireAjoutCarte = reactive({
    idColonne: "",
    titre: "",
    description: "",
    couleur: "",
    participants: [],
    idCarte: "",
    estEnvoye: false,

    /**
     * Fonction qui envoie le formulaire de création de carte et crée une carte dans l'API
     * @returns {Promise<void>} une promesse
     */
    envoyerFormulaire: async function () {
        if (this.estEnvoye || document.querySelector('.formulaireCreationCarte').getAttribute('data-modif') === 'true') {
            return;
        }
        this.estEnvoye = true;
        this.idColonne = escapeHtml(document.querySelector('.idColonne').value);

        if (this.idColonne !== '') {
            this.ajouterCarte(this.idColonne)
            closeForm();
            updateDraggables();
            updateCards();

            let response = await fetch(apiBase + '/carte/creer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    idColonne: this.idColonne,
                    titreCarte: escapeHtml(this.titre),
                    descriptifCarte: escapeHtml(this.description),
                    couleurCarte: escapeHtml(this.couleur),
                    affectationsCarte: this.participants
                })
            });

            if (response.status !== 200) {
                afficherMessageFlash('Erreur lors de la création de la carte.', 'danger');
            }
            this.estEnvoye = false;
            afficherMessageFlash('Carte créée avec succès.', 'success');
        } else {
            afficherMessageFlash('Erreur : informations manquantes pour la création de la carte.', 'danger');
        }
    },


    supprimerCarte: async function (idCarteidColonne) {

        let[idCarte, idColonne] = idCarteidColonne.split(',');

        const div = document.querySelector('.divSupprimerCarte');

        div.style.left = (event.clientX + window.scrollX) + 'px';
        div.style.top = (event.clientY + window.scrollY) + 'px';
        div.style.display = 'flex';

        document.addEventListener('click', function (e) {
            if (e.target !== div) {
                div.style.display = 'none';
            }
        });

        this.idCarte = idCarte;
        this.idColonne = idColonne;


        div.querySelector('span').addEventListener('click', async function () {

            document.querySelector(`[data-card="${idCarte}"]`).remove();
            div.style.display = 'none';
            let response = await fetch(apiBase + '/carte/supprimer', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    idCarte: idCarte
                })
            });

            if (response.status !== 200) {
                afficherMessageFlash('Erreur lors de la suppression de la carte.', 'danger');
            } else {
                afficherMessageFlash('Carte supprimée avec succès.', 'success');
                window.majUtilisateurs();
            }

        });
    },


    /**
     * Fonction qui ajoute une carte dans le DOM
     * @param idColonne l'id de la colonne dans laquelle ajouter la carte
     */
    ajouterCarte: async function (idColonne) {
        if (this) {
            const tousMembres = await getTousMembresTableaux(document.querySelector('.adder').getAttribute('data-tableau'));

            const id = await getNextIdCarte();

            let html = `<div class="card" draggable="true" data-onrightclick="formulaireAjoutCarte.supprimerCarte(${id})" data-card="${id}" data-colmuns="${idColonne}">
            <span class="color" style="border: 5px solid ${escapeHtml(this.couleur)}"></span>
            ${escapeHtml(this.titre)}
            <div class="features">`;

            for (let participant of tousMembres) {
                if (this.participants.includes(participant.login)) {
                    html += `<span class="user" data-user="${escapeHtml(participant.login)}">${escapeHtml(participant.prenom[0])}${escapeHtml(participant.nom[0])}</span>`;
                }
            }

            html += `</div>`;

            document.querySelector(`[data-columns="${idColonne}"] .stockage`).innerHTML += html;
            updateDraggables();
            startReactiveDom();
            changeCouleursPourUtilisateursSansCouleur();

        }
    },

    /**
     * Affiche les checkbox des participants d'un tableau pour le formulaire de création/modification de carte pour ajouter un membre à une carte
     * @returns {Promise<string>} la promesse habituelle et le contenu HTML à placer dans le htmlfun correspondant
     */
    afficherCheckBoxParticipants: async function () {
        const idTableau = document.querySelector('.adder').getAttribute('data-tableau');
        const estModif = escapeHtml(document.querySelector('.formulaireCreationCarte').getAttribute('data-modif'));


        if (estModif === 'true') {
            let idCarte = document.querySelector('.formulaireCreationCarte').getAttribute('data-carte');
            console.error("Pas encore implémenté");
        } else {
            this.idCarte = await getNextIdCarte();

            let response = await fetch(apiBase + `/tableau/membre/getPourTableau`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    idTableau: idTableau
                })
            });

            if (response.status !== 200) {
                afficherMessageFlash('Erreur lors de la récupération des membres du tableau.', 'danger');

            } else {

                let response2 = await fetch(apiBase + `/tableau/membre/getProprio`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        idTableau: idTableau
                    })
                });

                if (response2.status !== 200) {
                    afficherMessageFlash('Erreur lors de la récupération du propriétaire du tableau.', 'danger');
                } else {


                    const proprio = await response2.json();
                    let membres = await response.json();
                    document.querySelector('.listeParticipants').innerHTML = `<input data-onUncheck="formulaireAjoutCarte.supprimerParticipantCarte(${escapeHtml(proprio.login)})" data-oncheck="formulaireAjoutCarte.ajouterParticipantCarte(${escapeHtml(proprio.login)})" type="checkbox" data-participant="${escapeHtml(proprio.login)}" id="participant${escapeHtml(proprio.login)}" name="participant${escapeHtml(proprio.login)}" value="${escapeHtml(proprio.login)}">
                <label for="participant${escapeHtml(proprio.login)}" data-participant="${escapeHtml(proprio.login)}"><span class="user">${escapeHtml(proprio.prenom[0])}${escapeHtml(proprio.nom[0])}</span></label>`;

                    setTimeout(() => {
                        startReactiveDom();
                    }, 100);

                    if (membres.length === 0) {
                        return '<p>Il n\'y a pas de collaborateurs pour le moment</p><span class="addCollborateurs">Ajouter des collaborateurs</span>\n';
                    } else {
                        membres.forEach(membre => {
                            return document.querySelector('.listeParticipants').innerHTML += `<input data-onUncheck="formulaireAjoutCarte.supprimerParticipantCarte(${escapeHtml(membre.login)})" data-oncheck="formulaireAjoutCarte.ajouterParticipantCarte(${escapeHtml(membre.login)})" type="checkbox" data-participant="${escapeHtml(membre.login)}" id="participant${escapeHtml(membre.login)}" name="participant${escapeHtml(membre.login)}" value="${escapeHtml(membre.login)}">
                <label for="participant${escapeHtml(membre.login)}" data-participant="${escapeHtml(membre.login)}"><span class="user">${escapeHtml(membre.prenom[0])}${escapeHtml(membre.nom[0])}</span></label>`;
                        });
                    }
                }
            }
        }
    },

    /**
     * Fonction qui récupère les paramètres pour modifier une carte
     * @param parametres
     */
    setParametresPourModifier: function (parametres) {
        let [id, idCarte, titreCarte, descriptifCarte, couleurCarte] = parametres.split(',');
        this.idColonne = id;
        this.titre = titreCarte;
        this.description = descriptifCarte;
        this.couleur = couleurCarte;
        this.idCarte = idCarte;
        this.participants = window.affectationsCarte;
    },

    /**
     * Modifie une carte en front et dans l'API
     * @param idColonneIdCarte l'id de la colonne et de la carte à modifier
     * @returns {Promise<void>} une promesse
     */
    modifierCarte: async function (idColonneIdCarte = null) {
        if (this.titre !== '' && document.querySelector('.formulaireCreationCarte').getAttribute('data-modif') === 'true') {
            let html = `<span class="color" style="border: 5px solid ${this.couleur}"></span>
            ${this.titre}
            <div class="features">`;

            const tousMembres = await getTousMembresTableaux(document.querySelector('.adder').getAttribute('data-tableau'));

            for (let participant of tousMembres) {
                if (this.participants.includes(participant.login)) {
                    html += `<span class="user" data-user="${escapeHtml(participant.login)}">${escapeHtml(participant.prenom[0])}${escapeHtml(participant.nom[0])}</span>`;
                }
            }

            html += `</div>`;

            document.querySelector(`[data-card="${this.idCarte}"]`).innerHTML = html;

            randomColorsPourUsersDifferents();
            updateDraggables();
            updateCards();

            let response = await fetch(apiBase + '/carte/modifier', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    idCarte: escapeHtml(this.idCarte),
                    idColonne: escapeHtml(this.idColonne),
                    titreCarte: escapeHtml(this.titre),
                    descriptifCarte: escapeHtml(this.description),
                    couleurCarte: escapeHtml(this.couleur),
                    affectationsCarte: this.participants
                })
            });

            if (response.status !== 200) {
                afficherMessageFlash('Erreur lors de la modification de la carte.', 'danger');
            } else {
                afficherMessageFlash('Carte modifiée avec succès.', 'success');
            }
            closeForm();
            document.querySelector('.formulaireCreationCarte').removeAttribute('data-modif');
        } else {
            closeForm();
        }
    },


    /**
     * Ajoute un participant à une carte
     * @param idUtilisateur l'id de l'utilisateur à ajouter
     */
    ajouterParticipantCarte: function (idUtilisateur) {
        if (this && this.participants && !this.participants.includes(idUtilisateur)) {
            this.participants.push(idUtilisateur);
            if (this.idColonne === '') {
                this.idColonne = escapeHtml(document.querySelector('.idColonne').value);
            }
            window.ajouterCarteUtilisateur(this.idCarte, idUtilisateur, this.idColonne);
        }
    },

    /**
     * Supprime un participant d'une carte
     * @param idUtilisateur l'id de l'utilisateur à supprimer
     */
    supprimerParticipantCarte: function (idUtilisateur) {
        if (this && this.participants) {
            this.participants = this.participants.filter(participant => participant !== idUtilisateur);
            window.supprimerCarteUtilisateur(this.idCarte, idUtilisateur, this.idColonne);
        }
    }

}, "formulaireAjoutCarte");


/**
 * Fonction qui récupère tous les membres d'un tableau
 * @param idTableau l'id du tableau
 * @returns {Promise<*>} une promesse
 */
async function getTousMembresTableaux(idTableau) {
    let response = await fetch(apiBase + `/tableau/membre/getPourTableau`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            idTableau: escapeHtml(idTableau)
        })
    });

    let response2 = await fetch(apiBase + `/tableau/membre/getProprio`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            idTableau: escapeHtml(idTableau)
        })
    });

    if (response.status !== 200 || response2.status !== 200) {
        afficherMessageFlash('Erreur lors du chargement initial de la base de données.', 'danger')
    } else {
        const proprio = await response2.json();
        let membres = await response.json();
        return membres.concat(proprio);
    }


}


/**
 * Fonction qui récupère l'id de la prochaine carte disponible dans la BD
 */
async function getNextIdCarte() {
    let response = await fetch(apiBase + '/carte/nextid', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    });

    if (response.status !== 200) {
        afficherMessageFlash('Erreur lors de la récupération de l\'id de la future carte.', 'danger');
    }

    let idCarte = await response.json();
    return idCarte.idCarte - 1;
}

/**
 * Ferme le formulaire de création/modification de carte et réinitialise les champs
 */
function closeForm() {
    document.querySelector('.formulaireCreationCarte').style.display = "none";
    document.querySelector('.inputCreationCarte').value = '';
    document.querySelector('.listeParticipants').style.display = 'flex';
    document.querySelector('.listeNouveauxParticipants').style.display = 'none';
    document.querySelector('.titreCreationCarte').innerText = "Création d'une carte";
    document.querySelector('.boutonCreation').removeAttribute('data-onclick');
    document.querySelector('.boutonCreation').setAttribute('data-onclick', 'formulaireAjoutCarte.envoyerFormulaire');
    document.querySelector('.desc').value = '';
    document.querySelector('.boutonCreation').innerText = "Créer";
    document.querySelector('input[type="color"]').value = '#ffffff';
    document.querySelectorAll('.all').forEach(el => {
        el.style.opacity = '1';
    });
}

applyAndRegister(() => {
});

startReactiveDom();