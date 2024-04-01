import {applyAndRegister, reactive, startReactiveDom} from "./reactive.js";

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
        console.log('envoyerFormulaire');
        if (this.estEnvoye || document.querySelector('.formulaireCreationCarte').getAttribute('data-modif') === 'true') {
            return;
        }
        this.estEnvoye = true;
        this.idColonne = document.querySelector('.idColonne').value;

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
                    titreCarte: this.titre,
                    descriptifCarte: this.description,
                    couleurCarte: this.couleur,
                    affectationsCarte: this.participants
                })
            });

            if (response.status !== 200) {
                console.error("Erreur lors de la création de la carte dans l'API");
            }
            this.estEnvoye = false;
        } else {
            console.error("idColonne manquant.");
        }
    },


    supprimerCarte: async function (idCarte) {
        console.log(idCarte);
        const div = document.querySelector('.divSupprimerCarte');

        console.log(event.clientX, event.clientY);
        div.style.left = event.clientX + 'px';
        div.style.top = event.clientY + 'px';
        div.style.display = 'flex';

        document.addEventListener('click', function (e) {
            if (e.target !== div) {
                div.style.display = 'none';
            }
        });

        div.querySelector('span').addEventListener('click', async function () {
            document.querySelector(`[data-card="${idCarte}"], .card`).remove();
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
                console.error(response.error);
            } else {
                console.log(await response.json());
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

            let html = `<div class="card" draggable="true" data-card="${await getNextIdCarte()}" data-colmuns="${idColonne}">
            <span class="color" style="border: 5px solid ${this.couleur}"></span>
            ${this.titre}
            <div class="features">`;

            for (let participant of tousMembres) {
                if (this.participants.includes(participant.login)) {
                    html += `<span class="user" data-user="${participant.login}">${participant.prenom[0]}${participant.nom[0]}</span>`;
                }
            }

            html += `</div>`;

            document.querySelector(`[data-columns="${idColonne}"] .stockage`).innerHTML += html;
            updateDraggables();
            changeCouleursPourUtilisateursSansCouleur();

        }
    },

    /**
     * Affiche les checkbox des participants d'un tableau pour le formulaire de création/modification de carte pour ajouter un membre à une carte
     * @returns {Promise<string>} la promesse habituelle et le contenu HTML à placer dans le htmlfun correspondant
     */
    afficherCheckBoxParticipants: async function () {
        const idTableau = document.querySelector('.adder').getAttribute('data-tableau');
        const estModif = document.querySelector('.formulaireCreationCarte').getAttribute('data-modif');


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
                console.error(response.error);

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
                    console.error(response2.error);
                } else {


                    const proprio = await response2.json();
                    let membres = await response.json();
                    document.querySelector('.listeParticipants').innerHTML = `<input data-onUncheck="formulaireAjoutCarte.supprimerParticipantCarte(${proprio.login})" data-oncheck="formulaireAjoutCarte.ajouterParticipantCarte(${proprio.login})" type="checkbox" data-participant="${proprio.login}" id="participant${proprio.login}" name="participant${proprio.login}" value="${proprio.login}">
                <label for="participant${proprio.login}" data-participant="${proprio.login}"><span class="user">${proprio.prenom[0]}${proprio.nom[0]}</span></label>`;

                    setTimeout(() => {
                        startReactiveDom();
                    }, 100);

                    if (membres.length === 0) {
                        return '<p>Il n\'y a pas de collaborateurs pour le moment</p><span class="addCollborateurs">Ajouter des collaborateurs</span>\n';
                    } else {
                        membres.forEach(membre => {
                            return document.querySelector('.listeParticipants').innerHTML += `<input data-onUncheck="formulaireAjoutCarte.supprimerParticipantCarte(${membre.login})" data-oncheck="formulaireAjoutCarte.ajouterParticipantCarte(${membre.login})" type="checkbox" data-participant="${membre.login}" id="participant${membre.login}" name="participant${membre.login}" value="${membre.login}">
                <label for="participant${membre.login}" data-participant="${membre.login}"><span class="user">${membre.prenom[0]}${membre.nom[0]}</span></label>`;
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
            console.log('modifierCarte');
            let html = `<span class="color" style="border: 5px solid ${this.couleur}"></span>
            ${this.titre}
            <div class="features">`;

            const tousMembres = await getTousMembresTableaux(document.querySelector('.adder').getAttribute('data-tableau'));

            for (let participant of tousMembres) {
                if (this.participants.includes(participant.login)) {
                    html += `<span class="user" data-user="${participant.login}">${participant.prenom[0]}${participant.nom[0]}</span>`;
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
                    idCarte: this.idCarte,
                    idColonne: this.idColonne,
                    titreCarte: this.titre,
                    descriptifCarte: this.description,
                    couleurCarte: this.couleur,
                    affectationsCarte: this.participants
                })
            });
            console.log(await response.json());

            if (response.status !== 200) {
                console.error("Erreur lors de la modification de la carte dans l'API");
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
                this.idColonne = document.querySelector('.idColonne').value;
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
            idTableau: idTableau
        })
    });

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

    if (response.status !== 200 || response2.status !== 200) {
        console.error("Erreur lors de la récupération des membres du tableau");
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
        console.error((await response).json());
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