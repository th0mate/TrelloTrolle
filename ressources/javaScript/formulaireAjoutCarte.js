import {applyAndRegister, reactive, startReactiveDom} from "./reactive.js";

let formulaireAjoutCarte = reactive({
    idColonne: "",
    titre: "",
    description: "",
    couleur: "",

    envoyerFormulaire: function () {
        this.idColonne = document.querySelector('.idColonne').value;
        /*
        if (this.titre !== '') {
            //TODO : AJAX
        }
        */
        this.ajouterCarte(this.idColonne);
        document.querySelector('.formulaireCreationCarte').style.display = 'none';
        document.querySelector('.all').style.opacity = 1;
    },

    ajouterCarte: function (idColonne) {
        if (this) {
            return document.querySelector(`[data-columns="${idColonne}"] .stockage`).innerHTML += `<div class="card" draggable="true" data-colmuns="${idColonne}">
            <span class="color" style="border: 5px solid ${this.couleur}"></span>
            ${this.titre}
            <div class="features"></div>
            </div>`;
        } else {
            return document.querySelector(`[data-columns="${idColonne}"] .stockage`).innerHTML;
        }
    }

}, "formulaireAjoutCarte");

applyAndRegister(() => {
});

startReactiveDom();