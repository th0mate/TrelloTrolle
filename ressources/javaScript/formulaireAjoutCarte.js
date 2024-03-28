import {objectByName, applyAndRegister, reactive, startReactiveDom} from "./reactive.js";

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

    /**
     * Fonction qui envoie le formulaire de création de carte et crée une carte dans l'API
     * @returns {Promise<void>} une promesse
     */
    envoyerFormulaire: async function () {
        this.idColonne = document.querySelector('.idColonne').value;

        if (this.idColonne !== '') {
            this.ajouterCarte(this.idColonne)
            document.querySelector('.formulaireCreationCarte').style.display = 'none';
            document.querySelector('.all').style.opacity = 1;
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
                //TODO: Afficher un message d'erreur
            }

            document.querySelector('.idColonne').value = '';
            document.querySelector('.inputCreationCarte').value = '';
            document.querySelector('.desc').value = '';
            document.querySelector('.color').value = '#ffffff';
        } else {
            console.error("idColonne manquant.");
        }
    },


    /**
     * Fonction qui ajoute une carte dans le DOM
     * @param idColonne l'id de la colonne dans laquelle ajouter la carte
     */
    ajouterCarte: async function (idColonne) {
        if (this) {
            document.querySelector(`[data-columns="${idColonne}"] .stockage`).innerHTML += `<div class="card" draggable="true" data-card="${await getNextIdCarte()}" data-colmuns="${idColonne}">
            <span class="color" style="border: 5px solid ${this.couleur}"></span>
            ${this.titre}
            <div class="features"></div>
            </div>`;
            updateDraggables();
        }
    },

    afficherCheckBoxParticipants: async function () {
        const idTableau = document.querySelector('.adder').getAttribute('data-tableau');

        const estModif = document.querySelector('.formulaireCreationCarte').getAttribute('data-modif');


        if (estModif === 'true') {
            let idCarte = document.querySelector('.formulaireCreationCarte').getAttribute('data-carte');
            //TODO : Récupérer les participants de la carte
            console.error("Pas encore implémenté");
        } else {

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
                return 'aaa';

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

    ajouterParticipantCarte: function (idUtilisateur) {
        if (this && this.participants && !this.participants.includes(idUtilisateur)) {
            this.participants.push(idUtilisateur);
        }
    },

    supprimerParticipantCarte: function (idUtilisateur) {
        console.log('appel');
        if (this && this.participants) {
            this.participants = this.participants.filter(participant => participant !== idUtilisateur);
        }
    }

}, "formulaireAjoutCarte");


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
    return idCarte.idCarte;
}

applyAndRegister(() => {
});

startReactiveDom();