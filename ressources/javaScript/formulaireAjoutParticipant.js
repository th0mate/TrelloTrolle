import {applyAndRegister, reactive, startReactiveDom} from "./reactive.js";

let formulaireAjoutParticipant = reactive({
    idTableau: "",
    adresseMailARechercher: "",
    participants: [],

    rechercherDebutAdresseMail: async function () {
        if (this) {
            this.idTableau = document.querySelector('.formulaireAjoutMembreTableau').getAttribute('data-tableau');
            console.log(this.idTableau);
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
                // pour chaque adresse mail retournée par la bd, on ajoute un document.querySelector('.listeAjouter').innerHTML = <p data-idUtilisateur={id}>emaildeUtilisateur</p>
                let collaborateurs = await response.json();
                for (let collaborateur of collaborateurs) {
                    document.querySelector('.listeAjouter').innerHTML += `<p data-onlick="aa" data-idUtilisateur="${collaborateur.idUtilisateur}" data-onclick="formulaireAjoutParticipant.ajouterCheckboxPourUtilisateur(${collaborateur.idUtilisateur}, ${collaborateur.adresseMail}, true)">${collaborateur.adresseMail}</p>`;
                }
            }
        }
    },

    selectionnerParticipant: function (idUtilisateur) {

    },

    supprimerParticipant: function (idUtilisateur) {
        document.querySelector(`#participant${idUtilisateur}`).remove();
        if (this && this.participants) {
            this.participants = this.participants.filter(participant => participant !== idUtilisateur);
        }
    },

    ajouterCheckboxPourUtilisateur: function (idUtilisateur = null, value = null, estDepuisRecherche = false) {
        //data-oncheck="formulaireAjoutParticipant.ajouterParticipant(${idUtilisateur})"

        //TODO : corriger cette fonction
        if (this.idTableau !== '' && estDepuisRecherche) {
            this.ajouterParticipant(idUtilisateur);
            return document.querySelector('.checkBoxCollaborateurs').innerHTML += `<input checked data-onUncheck="formulaireAjoutParticipant.supprimerParticipant(${idUtilisateur})" type="checkbox" id="participant${idUtilisateur}" name="participant${idUtilisateur}" value="${value}">
        <label for="participant${idUtilisateur}"><span class="user">${value}</span></label>`;
        } else {
            return document.querySelector('.checkBoxCollaborateurs').innerHTML;
        }
    },

    ajouterParticipant: function (idUtilisateur) {
        if (this && this.participants) {
            this.participants.push(idUtilisateur);
        }
    },

    envoyerFormulaireAjoutMembre: async function () {
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