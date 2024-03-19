

import {applyAndRegister, reactive, startReactiveDom} from "./reactive.js";

let formulaireAjoutCarte = reactive({
    titre: "",
    description: "",
    couleur: "",

    envoyerFormulaire: function () {
        console.log("Titre : " + this.titre);
        console.log("Description : " + this.description);
        console.log("Couleur : " + this.couleur);
    }

}, "formulaireAjoutCarte");

applyAndRegister(() => {
});

startReactiveDom();