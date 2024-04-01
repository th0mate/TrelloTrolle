import {objectByName, applyAndRegister, reactive, startReactiveDom} from "./reactive.js";

let formulaireModificationTableau = reactive({
    titreTableau: "",


    modifierTableau: async function(idTableau){
        let response = await fetch("/tableau/modifier", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                idTableau: idTableau,
                titreTableau: this.titreTableau
            })
        });

        if (response.status !== 200) {
            afficherMessageFlash("Erreur lors de la modification du tableau", "danger")
        } else {
            afficherMessageFlash("Tableau modifié avec succès", "success")
        }
    }

}, "formulaireModificationTableau");

applyAndRegister({});

startReactiveDom();