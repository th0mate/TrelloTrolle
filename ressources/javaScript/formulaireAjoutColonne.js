import {applyAndRegister, reactive, startReactiveDom} from "./reactive.js";

let formulaireAjoutColonne = reactive({
    idTableau: "",
    idColonne: "",
    titre: "",

    /**
     * Crée une colonne en front et via l'API
     * @returns {Promise<void>} La promesse habituelle
     */
    envoyerFormulaireCreerColonne: async function () {

        if (this.titre !== '') {
            this.idTableau = document.querySelector('.adder').getAttribute('data-tableau');

            let response1 = await fetch(apiBase + '/colonne/nextid', {
                //retourne l'id de la colonne
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
            });

            if (response1.status !== 200) {
                console.error("Erreur lors de la création de la colonne dans l'API");
            }
            this.idColonne = await response1.json();
            this.idColonne = this.idColonne.idColonne;

            let newElement = document.createElement('div');
            newElement.classList.add('draggable');
            newElement.setAttribute('draggable', 'true');
            newElement.setAttribute('data-columns', this.idColonne);

            newElement.innerHTML = `<div class="entete"><h5 draggable="true" class="main">${this.titre}</h5><div class="bullets"><img src="${bulletsImageUrl}" alt=""></div></div><div data-columns="${this.idColonne}" class="stockage"></div><div class="add" data-columns="${this.idColonne}">
                <img src="${plusImageUrl}" alt="">
                Ajouter une carte
            </div>`;
            let ul = document.querySelector('.ul');
            ul.insertBefore(newElement, ul.lastElementChild);
            document.querySelector('.adder').value = '';
            updateDraggables();
            addEventsBullets(newElement);
            addEventsAdd();

            let response = await fetch(apiBase + '/colonne/creer', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    idTableau: this.idTableau,
                    nomColonne: this.titre
                })
            });

            if (response.status !== 200) {
                console.error("Erreur lors de la création de la colonne dans l'API");
            }
            document.querySelector('.input').value = '';
        }
    }

}, 'formulaireAjoutColonne');

applyAndRegister(() => {
});

startReactiveDom();