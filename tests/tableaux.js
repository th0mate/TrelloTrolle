/**
 * PARTIE GESTION DES DRAG AND DROP SUR LES COLONNES
 * @type {null}
 */

let dragSrcEl = null;
let btn = document.querySelector('.add');

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
    //AJAX ICI
    let input = document.getElementsByClassName('input')[0];
    let newElement = document.createElement('div');
    newElement.classList.add('draggable');
    newElement.setAttribute('draggable', 'true');
    if (input.value !== '') {
        newElement.innerHTML = `<h5 draggable="true" class="main">${input.value}</h5>`;
        let ul = document.querySelector('.ul');
        ul.insertBefore(newElement, ul.lastElementChild);
        input.value = '';
        updateDraggables();
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
 * PARTIE GESTION DES DRAG AND DROP DES CARTES
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
    if (this.classList.contains('card')) {
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
                console.log('coucou');
                this.appendChild(draggedCard);
            }
            draggedCard = null;
            updateCards();
        }
    }
}

updateCards();



