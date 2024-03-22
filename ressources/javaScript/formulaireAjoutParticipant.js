import {applyAndRegister, reactive, startReactiveDom} from "./reactive.js";

let formulaireAjoutParticipant = reactive({
    idTableau: "",
    adresseMailARechercher: "",
    participants: [],

    rechercherDebutAdresseMail: async function () {
        if (this && this.adresseMailARechercher) {
            this.adresseMailARechercher = document.querySelector('.adresseMailARechercher').value;
            let response = await fetch(apiBase + '/utilisateur/rechercher', {
                //TODO : faire la fonction API et le reste
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    idTableau: this.idTableau,
                    adresseMail: this.adresseMailARechercher
                })
            });

            if (response.status !== 200) {
                console.error(response.error);
            } else {
                console.log(response.responseText);
                //TODO : afficher les résultats
            }
        }
    },

    ajouterCheckboxPourUtilisateur: function (idUtilisateur, value) {
        return `<input data-oncheck="formulaireAjoutParticipant.ajouterParticipant(${idUtilisateur})" data-onUncheck="formulaireAjoutParticipant.supprimerParticipant(${idUtilisateur})" type="checkbox" id="participant${idUtilisateur}" name="participant${idUtilisateur}" value="${value}">
        <label for="participant${idUtilisateur}"><span class="user">${value}</span></label>`;
    },

    ajouterParticipant: function (idUtilisateur) {
        if (this && this.participants) {
            this.participants.push(idUtilisateur);
        }
    },

    supprimerParticipant: function (idUtilisateur) {
        if (this && this.participants) {
            this.participants = this.participants.filter(participant => participant !== idUtilisateur);
        }
    },

    envoyerFormulaire: async function () {
        if (this && this.participants) {
            //TODO : faire la fonction API
            let response = await fetch(apiBase + '/tableau/ajouterParticipant', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    idTableau: this.idTableau,
                    participants: this.participants
                })
            });

            if (response.status !== 200) {
                console.error(response.error);
            }
        }
    }

}, "formulaireAjoutParticipant");

applyAndRegister(() => {
});

startReactiveDom();