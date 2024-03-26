import {applyAndRegister, reactive, startReactiveDom} from "./reactive.js";

let formulaireModificationColonne = reactive({
    titre: "",
    idColonne: "",

    modifierColonne: async function () {
        if (this.titre !== '') {
            document.querySelector(`[data-columns="${document.querySelector('.menuColonnes').getAttribute('data-columns')}"] .main`).innerText = this.titre;
            updateDraggables();
        }

        document.querySelector('.formulaireModificationColonne').style.display = 'none';
        document.querySelectorAll('.all').forEach(el => {
            el.style.opacity = '1';
        });

        if (this.titre !=='') {
            let response = await fetch(apiBase + '/colonne/modifier', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    nomColonne: this.titre,
                    idColonne: document.querySelector('.menuColonnes').getAttribute('data-columns')
                })
            });

            if (response.status !== 200) {
                console.error("Erreur lors de la modification de la colonne dans l'API");
            }
        }
    }

}, "formulaireModificationColonne");

applyAndRegister({});

startReactiveDom();