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

    /**
     * Fonction qui envoie le formulaire de création de carte et crée une carte dans l'API
     * @returns {Promise<void>} une promesse
     */
    envoyerFormulaire: async function () {
        this.idColonne = document.querySelector('.idColonne').value;

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
                affectationsCarte: []
            })
        });

        if (response.status !== 200) {
            console.error("Erreur lors de la création de la carte dans l'API");
            //TODO: Afficher un message d'erreur
        }

        this.ajouterCarte(this.idColonne)
        document.querySelector('.formulaireCreationCarte').style.display = 'none';

        document.querySelector('.idColonne').value = '';
        document.querySelector('.inputCreationCarte').value = '';
        document.querySelector('.desc').value = '';
        document.querySelector('.color').value = '#ffffff';
        document.querySelector('.all').style.opacity = 1;
    },


    /**
     * Fonction qui ajoute une carte dans le DOM
     * @param idColonne l'id de la colonne dans laquelle ajouter la carte
     */
    ajouterCarte: function (idColonne) {
        if (this) {
            document.querySelector(`[data-columns="${idColonne}"] .stockage`).innerHTML += `<div class="card" draggable="true" data-colmuns="${idColonne}">
            <span class="color" style="border: 5px solid ${this.couleur}"></span>
            ${this.titre}
            <div class="features"></div>
            </div>`;
            updateDraggables();

        }
    }

}, "formulaireAjoutCarte");

applyAndRegister(() => {
});

startReactiveDom();