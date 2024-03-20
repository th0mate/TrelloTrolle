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
        this.ajouterCarte();
        document.querySelector('.formulaireCreationCarte').style.display = 'none';
        document.querySelector('.all').style.opacity = 1;
    },

    ajouterCarte: function () {
        return(`<div class="card" draggable="true" data-colmuns="${this.idColonne}">
            <span class="color" style="border: 5px solid ${this.couleur}"></span>
            ${this.titre}
            <div class="features"></div>
        </div>`);
    }

}, "formulaireAjoutCarte");

applyAndRegister(() => {
});

startReactiveDom();