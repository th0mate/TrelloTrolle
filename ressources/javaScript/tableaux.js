if (window.location.href.includes('tableau/')) {

    /**
     * ---------------------------------------------------------------------------------------------------------------------
     * PARTIE GESTION DES DRAG AND DROP SUR LES COLONNES
     * ---------------------------------------------------------------------------------------------------------------------
     */


    let dragSrcEl = null;
    let btn = document.querySelector('.addCard');

    let sourceIsMainOrDraggable = false;

    /**
     * Permet de stocker l'élément `.draggable` qui est en train d'être déplacé
     * @param e
     */
    function handleDragStart(e) {
        if (e.target.classList.contains('main')) {
            sourceIsMainOrDraggable = true;
            dragSrcEl = this;
            this.style.opacity = '0.4';
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', this.outerHTML);
            e.stopPropagation();
        } else {
            e.preventDefault();
        }
    }

    /**
     * Permet de prévenir le comportement par défaut de l'événement `dragover` et `drop` pour permettre le drop
     * @param e
     * @returns {boolean}
     */
    function handleDragOver(e) {
        if (sourceIsMainOrDraggable) {
            e.preventDefault();
            return false;
        }
    }

    /**
     * Permet de mettre la classe `over` à l'élément `.draggable` lorsque l'élément `.draggable` est survolé
     * @param e
     */
    function handleDragEnter(e) {
        if (sourceIsMainOrDraggable) {
            let targetDraggable = e.target.closest('.draggable');
            if (targetDraggable) {
                targetDraggable.classList.add('over');
            }
        }
    }

    /**
     * Permet de retirer la classe `over` à l'élément `.draggable` lorsque l'élément `.draggable` n'est plus survolé
     * @param e
     */
    function handleDragLeave(e) {
        if (sourceIsMainOrDraggable) {
            let targetDraggable = e.target.closest('.draggable');
            if (!targetDraggable || !targetDraggable.contains(e.relatedTarget)) {
                targetDraggable.classList.remove('over');
            }
        }
    }

    /**
     * Permet d'échanger le contenu de deux éléments `.draggable`
     * @param e
     */
    async function handleDrop(e) {
        if (sourceIsMainOrDraggable) {
            e.stopPropagation();
            for (let el of document.querySelectorAll('.draggable')) {
                el.classList.remove('over');
                el.style.opacity = '1';
            }
            let targetDraggable = e.target.closest('.draggable');
            if (dragSrcEl && this !== dragSrcEl && targetDraggable) {
                let droppedHTML = targetDraggable.innerHTML;
                let draggedHTML = dragSrcEl.innerHTML;
                dragSrcEl.innerHTML = droppedHTML;
                targetDraggable.innerHTML = draggedHTML;
                sourceIsMainOrDraggable = false;
                updateDraggables();
                addEventsBullets(dragSrcEl);
                addEventsBullets(targetDraggable);
                addEventsAdd();

                document.querySelector('.waiting').style.display = 'block';

                let response = await fetch(apiBase + '/colonne/inverser', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        idColonne1: targetDraggable.getAttribute('data-columns'),
                        idColonne2: dragSrcEl.getAttribute('data-columns')
                    })
                });

                if (response.status !== 200) {
                    afficherMessageFlash('Erreur lors de l\'inversion des colonnes.', 'danger')
                }

                document.querySelector('.waiting').style.display = 'none';

            }

        }
    }


    /**
     * Permet de retirer la classe `over` à l'élément `.draggable` lorsque l'élément `.draggable` n'est plus survolé
     * Est appelé quand le drop est effectué dans une zone non autorisée
     * @param e
     */
    function handleDragEnd(e) {
        if (sourceIsMainOrDraggable) {
            document.querySelectorAll('.draggable').forEach(el => {
                el.classList.remove('over');
                el.style.opacity = '1';
            });
        }
    }

    /**
     * Permet de mettre à jour les éléments `.draggable` pour qu'ils soient "draggable"
     */
    function updateDraggables() {
        let draggables = document.querySelectorAll('.draggable');
        draggables.forEach(el => {
            el.setAttribute('draggable', 'true');
            el.removeEventListener('dragstart', handleDragStart);
            el.removeEventListener('dragenter', handleDragEnter);
            el.removeEventListener('dragover', handleDragOver);
            el.removeEventListener('dragleave', handleDragLeave);
            el.removeEventListener('drop', handleDrop);
            el.removeEventListener('dragend', handleDragEnd);

            el.addEventListener('dragstart', handleDragStart, false);
            el.addEventListener('dragenter', handleDragEnter, false);
            el.addEventListener('dragover', handleDragOver, false);
            el.addEventListener('dragleave', handleDragLeave, false);
            el.addEventListener('drop', handleDrop, false);
            el.addEventListener('dragend', handleDragEnd, false);
            updateCards();
        });


    }

    updateDraggables();


    /**
     * ---------------------------------------------------------------------------------------------------------------------
     * PARTIE GESTION DES DRAG AND DROP DES CARTES
     * ---------------------------------------------------------------------------------------------------------------------
     */


    /**
     * Met à jour les éléments `.card` pour qu'ils soient "draggable"
     */
    function updateCards() {
        let cards = document.querySelectorAll('.card');
        let stockages = document.querySelectorAll('.stockage');

        cards.forEach(card => {
            card.setAttribute('draggable', 'true');
            card.addEventListener('dragstart', cardDragStart);
            card.addEventListener('dragend', cardDragEnd);
            card.addEventListener('dragover', cardDragOver);


            if (!card.hasAttribute('data-click-listener-added')) {
                card.addEventListener('click', function (e) {
                    document.querySelector('.formulaireCreationCarte').setAttribute('data-modif', 'true');
                    afficherFormulaireCreationCarte(card.closest('.stockage').getAttribute('data-columns'), true, card.getAttribute('data-card'));
                });
                card.setAttribute('data-click-listener-added', 'true');
            }
        });

        stockages.forEach(stockage => {
            stockage.addEventListener('dragover', function (e) {
                e.preventDefault();
                if (this.children.length === 0) {
                    this.classList.add('cardOver');
                }
            });
            stockage.addEventListener('dragenter', cardDragEnter);
            stockage.addEventListener('dragleave', cardDragLeave);
            stockage.addEventListener('drop', cardDrop);
            if (stockage.children.length > 0) {
                stockage.classList.add('isVoid');
            } else {
                stockage.classList.remove('isVoid');
            }
        });
    }

    let draggedCard = null;

    /**
     * Permet de stocker l'élément `.card` qui est en train d'être déplacé
     * @param e
     */
    function cardDragStart(e) {
        draggedCard = this;
        this.style.opacity = '0.4';
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', '');
        e.stopPropagation();
    }

    /**
     * Permet de retirer la classe `cardOver` à l'élément `.stockage` lorsque l'élément `.stockage` n'est plus survolé
     */
    function cardDragEnd() {
        this.style.opacity = "1";
        for (let el of document.querySelectorAll('.cardOver')) {
            el.classList.remove('cardOver');
        }
    }

    /**
     * Ajoute une classe `cardOver` à l'élément `.stockage` lorsque l'élément `.stockage` est survolé
     * @param e
     */
    function cardDragOver(e) {
        if (this.classList.contains('card') && !sourceIsMainOrDraggable) {
            e.preventDefault();
            this.classList.add('cardOver');
        }
    }


    /**
     * Permet de prévenir le comportement par défaut de l'événement `dragover` et `drop` pour permettre le drop
     * @param e
     */
    function cardDragEnter(e) {
        e.preventDefault();
    }

    /**
     * Permet de retirer la classe `cardOver` à l'élément `.stockage` lorsque l'élément `.stockage` n'est plus survolé
     */
    function cardDragLeave() {
        for (let el of document.querySelectorAll('.cardOver')) {
            el.classList.remove('cardOver');
        }
    }

    /**
     * Permet de gérer le drop des cartes
     * @param e {DragEvent} L'événement de drop
     */
    async function cardDrop(e) {
        if (!this.classList.contains('main')) {
            this.classList.remove('cardOver');
            if (draggedCard) {
                const idColonne = this.getAttribute('data-columns');
                const idCarte = draggedCard.getAttribute('data-card');

                if (this.children.length > 0) {
                    let targetCard = document.elementFromPoint(e.clientX, e.clientY).closest('.card');
                    if (targetCard) {
                        this.insertBefore(draggedCard, targetCard);
                        let rect = targetCard.getBoundingClientRect();
                        let x = rect.left + rect.width / 2;
                        let y = rect.top + rect.height / 2;
                        let evt = new MouseEvent('mousemove', {
                            clientX: x,
                            clientY: y
                        });
                        document.dispatchEvent(evt);

                    } else {
                        this.appendChild(draggedCard);
                    }
                } else {
                    this.appendChild(draggedCard);
                }
                draggedCard = null;
                updateCards();
                let response = await fetch(apiBase + '/carte/deplacer', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        idCarte: idCarte,
                        idColonne: idColonne
                    })
                });

                window.majUtilisateurs();

                if (response.status !== 200) {
                    afficherMessageFlash('Erreur lors du déplacement de la carte.', 'danger');
                }

            }
        }
    }

    updateCards();

    /**
     * ---------------------------------------------------------------------------------------------------------------------
     * PARTIE GESTION DES EVENEMENTS SUR LE CRUD DES CARTES ET DES COLONNES
     * ---------------------------------------------------------------------------------------------------------------------
     */


    /**
     * Ajoute les événements sur les éléments `.bullets`
     */
    function addEventsBullets(element) {
        element.querySelector('.bullets').addEventListener('click', function (e) {
            const menu = document.querySelector(".menuColonnes");
            if (menu) {
                if (menu.style.display === "flex") {
                    menu.style.display = "none";
                } else {
                    menu.style.top = e.clientY + "px";
                    menu.style.left = e.clientX + "px";
                    menu.style.display = "flex";
                    const id = this.closest('.draggable').getAttribute('data-columns');
                    menu.setAttribute('data-columns', id);
                }
            }
        });
    }

    /**
     * Evenement permettant de fermer le menu des colonnes
     */
    document.querySelector('.close').addEventListener('click', function () {
        document.querySelector('.menuColonnes').style.display = "none";
    });


    function supprimerToutesColonnesSansTitres() {
        document.querySelectorAll('.draggable').forEach(el => {
            if (!el.querySelector('.main').textContent) {
                el.remove();
            }
        });
    }

    supprimerToutesColonnesSansTitres();

    /**
     * Evenement permettant de supprimer une colonne
     */
    if (estProprio) {
        document.querySelector('.deleteColumn').addEventListener('click', async function () {
            const id = document.querySelector('.menuColonnes').getAttribute('data-columns');
            const draggableElement = document.querySelector(`.draggable[data-columns="${id}"]`);
            if (draggableElement) {
                draggableElement.remove();
            }
            document.querySelector('.menuColonnes').style.display = "none";

            let response = await fetch(apiBase + '/colonne/supprimer', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    idColonne: id
                })
            });

            if (response.status !== 200) {
                afficherMessageFlash('Erreur lors de la suppression de la colonne.', 'danger');
            } else {
                afficherMessageFlash('Colonne supprimée.', 'success');
            }
        });
    }

    /**
     * Evenement permettant d'afficher le formulaire de mise à jour d'une colonne
     */
    document.querySelector('.updateColumn').addEventListener('click', function () {
        const id = document.querySelector('.menuColonnes').getAttribute('data-columns');
        document.querySelector('.menuColonnes').style.display = "none";
        afficherFormulaireModificationColonne();
    });

    /**
     * Met à jour les colonnes
     */
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.draggable').forEach(el => {
            addEventsBullets(el);
            addEventsAdd();
        });
    });

    /**
     * Ajoute les écouteurs d'événements sur les éléments créés dynamiquement
     */
    function addEventListeners() {
        addEventsAdd();
    }

    /**
     * Permet d'ajouter des attributs et des EventListeners
     */
    function addEventsAdd() {
        document.querySelectorAll('.add').forEach(btn => {
            let newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
        });

        document.querySelectorAll('.add').forEach(btn => {
            btn.addEventListener('click', function (e) {
                const id = this.getAttribute('data-columns');
                afficherFormulaireCreationCarte(id);
            });
        });
    }

    /**
     * Ajoute une carte dans la colonne avec l'id `id` et la valeur `value`
     * @param id {string} L'id de la colonne
     * @param value {string} Le titre de la carte
     * @param color {string} La couleur de la carte
     */
    function ajouterCarte(id, value, color = 'white') {
        let stockageParent = null;

        for (let stockage of document.querySelectorAll('.stockage')) {
            if (stockage.getAttribute('data-columns') === id) {
                stockageParent = stockage;
                break;
            }
        }
        if (stockageParent) {
            let card = document.createElement('div');
            card.classList.add('card');
            card.setAttribute('draggable', 'true');
            card.setAttribute('data-colmuns', id);
            card.innerHTML = `<span class="color" style="border: 5px solid ${color}"></span>${escapeHtml(value)}<div class="features"></div>`;
            stockageParent.appendChild(card);

            let rect = card.getBoundingClientRect();
            let x = rect.left + rect.width / 2;
            let y = rect.top + rect.height / 2;
            let evt = new MouseEvent('mousemove', {
                clientX: x,
                clientY: y
            });
            document.dispatchEvent(evt);
            updateCards();
            addEventListeners();
            addListenersAjoutCard();
        } else {
            afficherMessageFlash('Erreur lors de l\'ajout de la carte.', 'danger');
        }
    }

    /**
     * Echappe les caractères spéciaux pour le HTML
     * @param text {string} Le texte à échapper
     * @returns {*} Le texte échappé
     */
    function escapeHtml(text) {
        // https://stackoverflow.com/questions/1787322/what-is-the-htmlspecialchars-equivalent-in-javascript
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }


    /**
     * Affiche le formulaire de création de carte/ ou de modification de carte pour la colonne avec l'id `id`
     * @param id {string} L'id de la colonne
     * @param pourModifier {boolean} Si le formulaire est affiché pour modifier une carte
     * @param idCarte {string} L'id de la carte à modifier
     */
    async function afficherFormulaireCreationCarte(id, pourModifier = false, idCarte = null) {

        document.querySelector('.idColonne').value = id;
        document.querySelector('.boutonCreation').removeAttribute('data-onclick');


        if (pourModifier) {
            document.querySelector('.waiting').style.display = 'block';
            let response = await fetch(apiBase + '/carte/getCarte', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    idCarte: idCarte
                })
            });

            let response2 = await fetch(apiBase + '/carte/affectations', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    idCarte: idCarte
                })
            });

            let response3 = await fetch(apiBase + '/tableau/membre/getPourTableau', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    idTableau: document.querySelector('.adder').getAttribute('data-tableau')
                })
            });

            let response4 = await fetch(apiBase + '/tableau/membre/getProprio', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    idTableau: document.querySelector('.adder').getAttribute('data-tableau')
                })
            });


            let users = await response3.json();
            let users2 = await response4.json();
            let affectations = await response2.json();

            let loginAffectations = [];
            for (let affectation of affectations) {
                loginAffectations.push(affectation.login);
            }

            const allUsers = users.concat(users2);

            if (response.status !== 200 || response2.status !== 200 || response3.status !== 200 || response4.status !== 200) {
                afficherMessageFlash('Erreur lors de la récupération des données.', 'danger')
            } else {
                let carte = await response.json();
                window.affectationsCarte = loginAffectations;
                document.querySelector('.formulaireCreationCarte').setAttribute('data-modif', 'true');
                document.querySelector('.formulaireCreationCarte').setAttribute('data-onload', `formulaireAjoutCarte.setParametresPourModifier(${id},${idCarte},${escapeHtml(carte.titreCarte)},${escapeHtml(carte.descriptifCarte)},${escapeHtml(carte.couleurCarte)}`);
                document.querySelector('.inputCreationCarte').value = carte.titreCarte;
                document.querySelector('.desc').value = carte.descriptifCarte;
                document.querySelector('input[type="color"]').value = carte.couleurCarte;
                document.querySelector('.listeParticipants').style.display = 'none';
                document.querySelector('.listeNouveauxParticipants').style.display = 'flex';
                document.querySelector('.titreCreationCarte').innerText = "Modifier la carte";
                document.querySelector('.boutonCreation').innerText = "Enregistrer";
                document.querySelector('.boutonCreation').setAttribute('data-onclick', `formulaireAjoutCarte.modifierCarte`);


                document.querySelector('.waiting').style.display = 'none';
                let html = '';
                for (let affectation of affectations) {
                    html += `<input data-onUncheck="formulaireAjoutCarte.supprimerParticipantCarte(${affectation.login})" data-oncheck="formulaireAjoutCarte.ajouterParticipantCarte(${affectation.login})" type="checkbox" data-participant="${affectation.login}" id="participant${affectation.login}" name="participant${affectation.login}" checked value="${escapeHtml(affectation.login)}">
                <label for="participant${affectation.login}" data-participant="${affectation.login}"><span class="user">${escapeHtml(affectation.prenom[0])}${escapeHtml(affectation.nom[0])}</span></label>`;
                }

                for (let user of allUsers) {
                    let trouve = false;
                    for (let affectation of affectations) {
                        if (affectation.login === user.login) {
                            trouve = true;
                            break;
                        }
                    }
                    if (!trouve) {
                        html += `<input data-onUncheck="formulaireAjoutCarte.supprimerParticipantCarte(${user.login})" data-oncheck="formulaireAjoutCarte.ajouterParticipantCarte(${user.login})" type="checkbox" data-participant="${user.login}" id="participant${user.login}" name="participant${user.login}" value="${escapeHtml(user.login)}">
                <label for="participant${user.login}" data-participant="${user.login}"><span class="user">${escapeHtml(user.prenom[0])}${escapeHtml(user.nom[0])}</span></label>`;
                    }
                }

                document.querySelector('.listeNouveauxParticipants').innerHTML = html;
            }
        } else {
            document.querySelector('.boutonCreation').setAttribute('data-onclick', `formulaireAjoutCarte.envoyerFormulaire`);
        }
        window.startReactiveDom();
        randomColorsNewUsers();
        document.querySelector('.formulaireCreationCarte').style.display = "flex";

        document.querySelectorAll('.all').forEach(el => {
            el.style.opacity = '0.5';
        });
        addListenersAjoutCard(id);
    }

    /**
     * Ajoute les écouteurs d'événements sur les éléments pour le formulaire d'ajout des cartes
     * @param id {string} L'id de la colonne
     */
    function addListenersAjoutCard(id) {
        document.querySelectorAll('.closeCard').forEach(function (closeCard) {
            let newCloseCard = closeCard.cloneNode(true);
            closeCard.parentNode.replaceChild(newCloseCard, closeCard);

            newCloseCard.addEventListener('click', function () {
                document.querySelector('.formulaireCreationCarte').style.display = "none";

                document.querySelector('.inputCreationCarte').value = '';
                document.querySelector('.listeParticipants').style.display = 'flex';
                document.querySelector('.listeNouveauxParticipants').style.display = 'none';
                document.querySelector('.titreCreationCarte').innerText = "Création d'une carte";
                document.querySelector('.boutonCreation').removeAttribute('data-onclick');
                document.querySelector('.boutonCreation').setAttribute('data-onclick', 'formulaireAjoutCarte.envoyerFormulaire');
                document.querySelector('.desc').value = '';
                document.querySelector('.boutonCreation').innerText = "Créer";
                document.querySelector('input[type="color"]').value = '#ffffff';
                document.querySelectorAll('.all').forEach(el => {
                    el.style.opacity = '1';
                });
            });
        });
    }


    /**
     * Affiche un formulaire de modification de colonne
     */
    function afficherFormulaireModificationColonne() {
        const valeurActuelle = document.querySelector(`[data-columns="${document.querySelector('.menuColonnes').getAttribute('data-columns')}"] .main`).innerText;
        document.querySelector('.formulaireModificationColonne').style.display = "flex";
        document.querySelectorAll('.all').forEach(el => {
            el.style.opacity = '0.5';
        });
        document.querySelector('.inputModificationColonne').value = valeurActuelle;
        addListenersModificationColonne();
    }


    /**
     * Ajoute les écouteurs d'événements sur les éléments pour le formulaire de modification de colonne
     */
    function addListenersModificationColonne() {
        document.querySelectorAll('.closeColumn').forEach(function (closeColumn) {
            let newCloseColumn = closeColumn.cloneNode(true);
            closeColumn.parentNode.replaceChild(newCloseColumn, closeColumn);

            newCloseColumn.addEventListener('click', function () {
                document.querySelector('.formulaireModificationColonne').style.display = "none";
                document.querySelectorAll('.all').forEach(el => {
                    el.style.opacity = '1';
                });
            });
        });
    }

    /**
     * ---------------------------------------------------------------------------------------------------------------------
     * PARTIE GESTION DES EVENEMENTS ET DES ELEMENTS SUR LES UTILISATEURS
     * ---------------------------------------------------------------------------------------------------------------------
     */

    /**
     * Retourne une couleur aléatoire sombre
     */
    function randomColor() {
        let letters = '012345678';
        let color = '#';
        for (let i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * letters.length)];
        }
        return color;
    }

    /**
     * Met une couleur aléatoire sombre pour chaque utilisateur différent dans la page
     */
    function randomColorsPourUsersDifferents() {
        document.querySelectorAll('[data-user]').forEach(el => {
            const listeLoginDejaPasses = [];
            document.querySelectorAll(`[data-user="${el.getAttribute('data-user')}"]`).forEach(el2 => {
                if (!listeLoginDejaPasses.includes(el2.getAttribute('data-user'))) {
                    el2.style.backgroundColor = randomColor();
                    listeLoginDejaPasses.push(el2.getAttribute('data-user'));
                    listeLoginDejaPasses.push(el2.style.backgroundColor);
                } else {
                    el2.style.backgroundColor = listeLoginDejaPasses[listeLoginDejaPasses.indexOf(el2.getAttribute('data-user')) + 1];
                }
            });
        })
    }

    document.addEventListener('DOMContentLoaded', function () {
        randomColorsPourUsersDifferents();
    });


    /**
     * Repasse sur toute la page pour mettre à jour les couleurs de chaque user différent, sans changer les couleurs déjà attribuées, sauf si l'utilisateur n'a pas de couleur
     */
    function changeCouleursPourUtilisateursSansCouleur() {
        document.querySelectorAll('[data-user]').forEach(el => {
            const listeLoginDejaPasses = [];
            document.querySelectorAll(`[data-user="${el.getAttribute('data-user')}"]`).forEach(el2 => {
                if (!el2.style.backgroundColor) {
                    if (!listeLoginDejaPasses.includes(el2.getAttribute('data-user'))) {
                        el2.style.backgroundColor = randomColor();
                        listeLoginDejaPasses.push(el2.getAttribute('data-user'));
                        listeLoginDejaPasses.push(el2.style.backgroundColor);
                    } else {
                        el2.style.backgroundColor = listeLoginDejaPasses[listeLoginDejaPasses.indexOf(el2.getAttribute('data-user')) + 1];
                    }
                } else {
                    listeLoginDejaPasses.push(el2.getAttribute('data-user'));
                    listeLoginDejaPasses.push(el2.style.backgroundColor);
                }
            });
        })
    }


    /**
     * Met une couleur aléatoire à tous les éléments '.user' n'ayant pas d'attribut 'data-user'
     */
    function randomColorsNewUsers() {
        document.querySelectorAll('.user').forEach(el => {
            if (!el.hasAttribute('data-user')) {
                el.style.backgroundColor = randomColor();
            }
        });
    }


    /**
     * Défini l'événement de clic pour ajouter des participants à un tableau
     */
    if (estProprio) {
        document.querySelector('.invite').addEventListener('click', function () {
            document.querySelector('.formulaireAjoutMembreTableau').style.display = "flex";
            document.querySelectorAll('.all').forEach(el => {
                el.style.opacity = '0.5';
            });
        });
    }

    /**
     * Défini l'événement de clic pour fermer le formulaire d'ajout de membre
     */
    function listenerFermerAjoutMembre() {
        document.querySelector('.formulaireAjoutMembreTableau').style.display = "none";
        document.querySelectorAll('.all').forEach(el => {
            el.style.opacity = '1';
        });
    }
}
