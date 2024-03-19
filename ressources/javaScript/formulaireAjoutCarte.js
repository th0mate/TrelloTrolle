import {applyAndRegister, reactive, startReactiveDom} from "./reactive.js";

let formulaireAjoutCarte = reactive({
    idColonne: "",
    titre: "",
    description: "",
    couleur: "",

    envoyerFormulaire: function () {
        this.idColonne = document.querySelector('.idColonne').value;
        console.log('idColonne :' + this.idColonne);
        console.log("Titre : " + this.titre);
        console.log("Description : " + this.description);
        console.log("Couleur : " + this.couleur);

        if (this.titre !== '') {
            //TODO : AJAX
        }
        document.querySelector('.formulaireCreationCarte').style.display = 'none';
        document.querySelector('.all').style.opacity = 1;
    }

}, "formulaireAjoutCarte");

applyAndRegister(() => {
});

startReactiveDom();