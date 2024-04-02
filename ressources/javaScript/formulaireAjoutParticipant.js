import {applyAndRegister, reactive, startReactiveDom} from "./reactive.js";

let formulaireAjoutParticipant = reactive({
    idTableau: "",
    adresseMailARechercher: "",
    participants: [],

    /**
     * Recherche les utilisateurs dont l'adresse mail/login/nom/prénom commence par le texte entré
     * @returns {Promise<void>} La promesse habituelle
     */
    rechercherDebutAdresseMail: async function () {
        this.idTableau = document.querySelector('.formulaireAjoutMembreTableau').getAttribute('data-tableau');
        if (this.adresseMailARechercher !== '' && this.adresseMailARechercher.length > 2) {
            let response = await fetch(apiBase + '/utilisateur/recherche', {

                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    recherche: escapeHtml(this.adresseMailARechercher),
                })
            });

            if (response.status !== 200) {
                afficherMessageFlash("Erreur lors de la recherche d'un utilisateur.", "danger")
            } else {
                let collaborateurs = await response.json();
                document.querySelector('.listeAjouter').innerHTML = '';
                for (let collaborateur of collaborateurs) {
                    document.querySelector('.listeAjouter').innerHTML += `<p data-login="${escapeHtml(collaborateur.login)}" data-onclick="formulaireAjoutParticipant.ajouterCheckboxPourUtilisateur(${escapeHtml(collaborateur.login)}, ${escapeHtml(collaborateur.prenom[0])}${escapeHtml(collaborateur.nom[0])}, true)">${escapeHtml(collaborateur.prenom)} ${escapeHtml(collaborateur.nom)}</p>`;
                }
                startReactiveDom();
            }
        }
    },


    /**
     * Supprime un participant du formulaire
     * @param idUtilisateur l'id de l'utilisateur à supprimer
     */
    supprimerParticipant: function (idUtilisateur) {
        let elements = document.querySelectorAll(`[data-participant="${idUtilisateur}"]`);
        for (let element of elements) {
            element.remove();
        }
        if (this && this.participants) {
            this.participants = this.participants.filter(participant => participant !== idUtilisateur);
        }
    },

    /**
     * Ajoute un checkbox pour un utilisateur
     * @param parametres les paramètres de l'utilisateur
     * @returns {string} le code html du checkbox placé dans le htmlfun correspondant
     */
    ajouterCheckboxPourUtilisateur: function (parametres = null) {

        if (parametres !== null) {

            let [idUtilisateur, value, estDepuisRecherche] = parametres.split(',');
            if (this.idTableau !== '' && estDepuisRecherche && !this.participants.includes(idUtilisateur)) {

                this.ajouterParticipant(idUtilisateur);
                document.querySelector('.listeAjouter').innerHTML = '';
                document.querySelector('.inputAjoutMembre').value = '';

                setTimeout(() => {
                    startReactiveDom();
                    randomColorsNewUsers();
                }, 100);

                return document.querySelector('.checkBoxCollaborateurs').innerHTML += `<input data-onUncheck="formulaireAjoutParticipant.supprimerParticipant(${idUtilisateur})" type="checkbox" data-participant="${idUtilisateur}" checked id="participant${idUtilisateur}" name="participant${idUtilisateur}" value="${escapeHtml(value)}">
        <label for="participant${idUtilisateur}" data-participant="${idUtilisateur}"><span class="user">${escapeHtml(value)}</span></label>`;

            } else {

                document.querySelector('.listeAjouter').innerHTML = '';
                document.querySelector('.inputAjoutMembre').value = '';
                return document.querySelector('.checkBoxCollaborateurs').innerHTML;
            }
        } else {
            return document.querySelector('.checkBoxCollaborateurs').innerHTML;
        }
    },

    ajouterParticipant: function (idUtilisateur) {
        if (this && this.participants) {
            this.participants.push(idUtilisateur);
        }
    },


    /**
     * Ajoute le ou les membres au tableau en front et via l'API
     * @returns {Promise<void>} La promesse habituelle
     */
    envoyerFormulaireAjoutMembre: async function () {
        if (this && this.participants) {
            let response = await fetch(apiBase + `/tableau/membre/ajouter`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    idTableau: this.idTableau,
                    login: this.participants
                })
            });

            if (response.status !== 200) {
                afficherMessageFlash("Erreur lors de l'ajout de membres au tableau", "danger")
            } else {
                afficherMessageFlash("Membre(s) ajouté(s) avec succès au tableau", "success")
            }
            window.majUtilisateurs();
            document.querySelector('.formulaireAjoutMembreTableau').style.display = 'none';
            document.querySelector('.all').style.opacity = '1';
        }
    }

}, "formulaireAjoutParticipant");

applyAndRegister(() => {
});

startReactiveDom();