import {objectByName, applyAndRegister, reactive, startReactiveDom} from "./reactive.js";

let formulaireAjoutCarte = reactive({
    idColonne: "",
    titre: "",
    description: "",
    couleur: "",

    envoyerFormulaire: async function () {


        this.idColonne = document.querySelector('.idColonne').value;

        let response = await fetch(apiBase + '/carte/creer', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                idColonne: this.idColonne,
                titreCarte: this.titre,
                descriptifCarte: this.description,
                couleurCarte: this.couleur,
                affectationCarte: null
            })
        });

        if (response.status !== 200) {
            console.error("Erreur lors de la création de la carte");
            //TODO: Afficher un message d'erreur
        }
        console.log( response);
        console.log( response.responseText);

        this.ajouterCarte(this.idColonne)
        document.querySelector('.formulaireCreationCarte').style.display = 'none';

        document.querySelector('.idColonne').value = '';
        document.querySelector('.inputCreationCarte').value = '';
        document.querySelector('.desc').value = '';
        document.querySelector('.color').value = '';
        document.querySelector('.all').style.opacity = 1;
    },


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