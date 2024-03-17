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
        console.log('handleDragEnd');
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

/**
 * Permet d'ajouter un nouvel élément `.draggable` dans la page
 */
function addNewItem() {
    //TODO : AJAX ICI

    //temporaire - prend le premier id disponible non présent dans la page
    let id = 1;
    let ids = [];
    document.querySelectorAll('.stockage').forEach(stockage => {
        ids.push(parseInt(stockage.getAttribute('data-columns')));
    });
    while (ids.includes(id)) {
        id++;
    }
    let input = document.getElementsByClassName('input')[0];
    let newElement = document.createElement('div');
    newElement.classList.add('draggable');
    newElement.setAttribute('draggable', 'true');
    newElement.setAttribute('data-columns', id);
    if (input.value !== '') {
        newElement.innerHTML = `<div class="entete"><h5 draggable="true" class="main">${input.value}</h5><div class="bullets"><img src="bullets.png" alt=""></div></div><div data-columns="${id}" class="stockage"></div><div class="add" data-columns="${id}">
                <img src="plus.png" alt="">
                Ajouter une carte
            </div>`;
        let ul = document.querySelector('.ul');
        ul.insertBefore(newElement, ul.lastElementChild);
        input.value = '';
        updateDraggables();
        addEventsBullets(newElement);
        addEventsAdd();
    }
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && document.activeElement.classList.contains('input')) {
        addNewItem();
    }
});

btn.addEventListener('click', addNewItem);


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
            //TODO : AJAX ICI
        }
    }
}

updateCards();

/**
 * ---------------------------------------------------------------------------------------------------------------------
 * PARTIE GESTION DES EVENEMENTS SUR LE CRUD DES CARTES
 * ---------------------------------------------------------------------------------------------------------------------
 */


/**
 * Ajoute les événements sur les éléments `.bullets`
 */
function addEventsBullets(element) {
    element.querySelector('.bullets').addEventListener('click', function (e) {
        console.log(this.closest('.draggable').getAttribute('data-columns'));
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

addEventsBullets();

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
        card.innerHTML = `<span style="border: 5px solid ${color}"></span>${value}<div class="features"></div>`;
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
 */
function afficherFormulaireCreationCarte(id) {
    console.log(id);
    const html = '<div class="formulaireCreationCarte">' +
        '<div class="wrap"><h2>Création d\'une carte</h2><img class="closeCard" src="close.png" alt=""></div>' +
        '<div class="content"><h4>Titre de la carte :</h4><input maxlength="50" required type="text" class="inputCreationCarte" placeholder="Entrez le titre de la carte"></div>' +
        '<div class="content"><h4>Description de la carte :</h4><textarea maxlength="255" class="desc" placeholder="Description de la carte..." ></textarea></div>' +
        '<div class="content"><h4>Couleur de la carte :</h4><input required type="color"></div>' +
        '<div class="content"><h4>Membres affectés :</h4><input type="text"></div>' +
        '<div class="boutonCreation">Créer</div>' +
        '</div>';

    document.body.insertAdjacentHTML('beforeend', html);
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
            document.querySelector('.formulaireCreationCarte').remove();
            document.querySelectorAll('.all').forEach(el => {
                el.style.opacity = '1';
            });
        });
    });

    document.querySelectorAll('.boutonCreation').forEach(function (boutonCreation) {
        let newBoutonCreation = boutonCreation.cloneNode(true);
        boutonCreation.parentNode.replaceChild(newBoutonCreation, boutonCreation);

        newBoutonCreation.addEventListener('click', function () {
            const titre = document.querySelector('.inputCreationCarte').value;
            const description = document.querySelector('.desc').value;
            const couleur = document.querySelector('input[type="color"]').value;
            //TODO : AJAX ICI
            if (titre !== '') {
                ajouterCarte(id, titre, couleur);
            }

            if (document.querySelector('.formulaireCreationCarte')) {
                document.querySelector('.formulaireCreationCarte').remove();
            }
            document.querySelectorAll('.all').forEach(el => {
                el.style.opacity = '1';
            });
        });
    });
}

