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
        this.ajouterCarte(this.idColonne)
        document.querySelector('.formulaireCreationCarte').style.display = 'none';
        //on vide tous les inputs de formulaireCreationCarte
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