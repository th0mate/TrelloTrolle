import {applyAndRegister, reactive, startReactiveDom} from "./reactive.js";

let formulaireAjoutParticipant = reactive({
    idTableau: "",
    adresseMailARechercher: "",
    participants: [],

    rechercherDebutAdresseMail: async function () {
        this.idTableau = document.querySelector('.formulaireAjoutMembreTableau').getAttribute('data-tableau');
        if (this.adresseMailARechercher !== '' && this.adresseMailARechercher.length > 3) {
            let response = await fetch(apiBase + '/utilisateur/recherche', {

                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    recherche: this.adresseMailARechercher,
                })
            });

            if (response.status !== 200) {
                console.error(response.error);
            } else {
                let collaborateurs = await response.json();
                document.querySelector('.listeAjouter').innerHTML = '';
                for (let collaborateur of collaborateurs) {
                    document.querySelector('.listeAjouter').innerHTML += `<p data-idUtilisateur="${collaborateur.login}" data-onclick="formulaireAjoutParticipant.ajouterCheckboxPourUtilisateur('${collaborateur.login}', '${collaborateur.prenom[0]}${collaborateur.nom[0]}', true)">${collaborateur.prenom} ${collaborateur.nom}</p>`;
                }
            }
        }
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
        console.log(estDepuisRecherche);
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