import {objectByName, applyAndRegister, reactive, startReactiveDom} from "./reactive.js";

let formulaireModificationTableau = reactive({
    titreTableau: "",


    /**
     * Affiche le formulaire de modification du tableau
     */
    afficherFormulaireModificationTableau: function () {
        document.querySelector('.inputModificationTableau').value = escapeHtml(document.querySelector('.titreTableau').textContent);
        document.querySelector('.formulaireModificationTableau').style.display = "flex";
        document.querySelector('.all').style.opacity = "0.5";

        document.querySelector('.closeTable').addEventListener('click', () => {
            document.querySelector('.formulaireModificationTableau').style.display = "none";
            document.querySelector('.all').style.opacity = "1";
        });
    },


    /**
     * Modifie le titre du tableau en front et via l'API
     * @param idTableau l'id du tableau à modifier
     * @returns {Promise<void>} La promesse habituelle
     */
    modifierTableau: async function (idTableau) {
        this.titreTableau = escapeHtml(this.titreTableau);
        if (this.titreTableau !== '') {
            let response = await fetch(apiBase + "/tableau/modifier", {
                method: "PATCH",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    idTableau: idTableau,
                    nomTableau: this.titreTableau
                })
            });
            if (response.status !== 200) {
                afficherMessageFlash("Erreur lors de la modification du tableau", "danger")
            } else {
                afficherMessageFlash("Tableau modifié avec succès", "success")
            }
        }
        document.querySelector('.titreTableau').textContent = this.titreTableau;
        document.querySelector('.formulaireModificationTableau').style.display = "none";
        document.querySelector('.all').style.opacity = "1";
    }

}, "formulaireModificationTableau");

applyAndRegister({});

startReactiveDom();