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
function handleDrop(e) {
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
function cardDrop(e) {
    if (!this.classList.contains('main')) {
        this.classList.remove('cardOver');
        if (draggedCard) {
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

document.querySelector('.close').addEventListener('click', function () {
    document.querySelector('.menuColonnes').style.display = "none";
});


/**
 * Evenement permettant de suppimer une colonne
 */
document.querySelector('.deleteColumn').addEventListener('click', function () {
    const id = document.querySelector('.menuColonnes').getAttribute('data-columns');
    const draggableElement = document.querySelector(`.draggable[data-columns="${id}"]`);
    if (draggableElement) {
        draggableElement.remove();
    }
    let response = fetch(apiBase + '/colonne/supprimer', {
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
        console.error(response.error);
    }

    document.querySelector('.menuColonnes').style.display = "none";
});

document.querySelector('.updateColumn').addEventListener('click', function () {
    const id = document.querySelector('.menuColonnes').getAttribute('data-columns');
    document.querySelector('.menuColonnes').style.display = "none";
    afficherFormulaireModificationColonne();
});


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
        card.innerHTML = `<span class="color" style="border: 5px solid ${color}"></span>${value}<div class="features"></div>`;
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
        console.error('Aucune colonne avec l\'id ' + id + ' n\'a été trouvée');
    }
}

/**
 * Affiche le formulaire de création de carte pour la colonne avec l'id `id`
 * @param id {string} L'id de la colonne
 * @param pourModifier {boolean} Si le formulaire est affiché pour modifier une carte
 */
function afficherFormulaireCreationCarte(id, pourModifier = false) {

    document.querySelector('.formulaireCreationCarte').style.display = "flex";
    document.querySelector('.idColonne').value = id;
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
            document.querySelector('.desc').value = '';
            document.querySelector('input[type="color"]').value = '#ffffff';
            document.querySelectorAll('.all').forEach(el => {
                el.style.opacity = '1';
            });
        });
    });
}

function fermeFormulaireEtAjouteCarte(id) {
    const titre = document.querySelector('.inputCreationCarte').value;
    const description = document.querySelector('.desc').value;
    const couleur = document.querySelector('input[type="color"]').value;

    if (titre !== '') {
        ajouterCarte(id, titre, couleur);
    }

    document.querySelector('.formulaireCreationCarte').style.display = "none";
    document.querySelectorAll('.all').forEach(el => {
        el.style.opacity = '1';
    });
}


/**
 * Affiche un formulaire de modification de colonne
 */
function afficherFormulaireModificationColonne() {
    const valeurActuelle = document.querySelector(`[data-columns="${document.querySelector('.menuColonnes').getAttribute('data-columns')}"] .main`).innerText;
    const html = '<div class="formulaireModificationColonne">' +
        '<div class="wrap"><h2>Modification de la colonne</h2><img class="closeColumn" src="../tests/close.png" alt=""></div>' +
        '<div class="content"><h4>Nouveau titre :</h4><input maxlength="50" required type="text" value="' + valeurActuelle + '" class="inputModificationColonne" placeholder="Entrez le nouveau titre"></div>' +
        '<div class="boutonModification">Modifier</div>' +
        '</div>';

    document.body.insertAdjacentHTML('beforeend', html);
    document.querySelectorAll('.all').forEach(el => {
        el.style.opacity = '0.5';
    });
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
            document.querySelector('.formulaireModificationColonne').remove();
            document.querySelectorAll('.all').forEach(el => {
                el.style.opacity = '1';
            });
        });
    });

    document.querySelectorAll('.boutonModification').forEach(function (boutonModification) {
        let newBoutonModification = boutonModification.cloneNode(true);
        boutonModification.parentNode.replaceChild(newBoutonModification, boutonModification);

        newBoutonModification.addEventListener('click', function () {
            const titre = document.querySelector('.inputModificationColonne').value;
            if (titre !== '') {
                document.querySelector('.formulaireModificationColonne').remove();
                document.querySelectorAll('.all').forEach(el => {
                    el.style.opacity = '1';
                });

                document.querySelector(`[data-columns="${document.querySelector('.menuColonnes').getAttribute('data-columns')}"] .main`).innerText = titre;
                updateCards();
                addEventsAdd();
            }
        });
    });
}

/**
 * ---------------------------------------------------------------------------------------------------------------------
 * PARTIE GESTION DES EVENEMENTS ET DES ELEMENTS SUR LES UTILISATEURS
 * ---------------------------------------------------------------------------------------------------------------------
 */

/**
 * Retourne une couleur aléatoire
 */
function randomColor() {
    let letters = '0123456789ABCDEF';
    let color = '#';
    for (let i = 0; i < 3; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.user').forEach(el => {
        el.style.backgroundColor = randomColor();
    });
});


document.querySelector('.invite').addEventListener('click', function () {
    document.querySelector('.formulaireAjoutMembreTableau').style.display = "flex";
    document.querySelectorAll('.all').forEach(el => {
        el.style.opacity = '0.5';
    });
});

document.querySelector('.addCollborateurs').addEventListener('click', function () {
    document.querySelector('.formulaireAjoutMembreTableau').style.display = "flex";
    document.querySelector('.formulaireCreationCarte').style.display = "none";
    document.querySelectorAll('.all').forEach(el => {
        el.style.opacity = '0.5';
    });
});


function listenerFermerAjoutMembre() {
    document.querySelector('.formulaireAjoutMembreTableau').style.display = "none";
    document.querySelectorAll('.all').forEach(el => {
        el.style.opacity = '1';
    });

}