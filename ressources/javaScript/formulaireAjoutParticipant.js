import {applyAndRegister, reactive, startReactiveDom} from "./reactive.js";

let formulaireAjoutParticipant = reactive({
    idTableau: "",
    adresseMailARechercher: "",

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
    }

}, "formulaireAjoutParticipant");

applyAndRegister(() => {
});

startReactiveDom();