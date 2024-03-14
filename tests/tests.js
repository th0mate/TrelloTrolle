/**
 * PARTIE GESTION DES DRAG AND DROP SUR LES COLONNES
 * @type {null}
 */

let dragSrcEl = null;
let btn = document.querySelector('.add');

/**
 * Permet de stocker l'élément `.draggable` qui est en train d'être déplacé
 * @param e
 */
function handleDragStart(e) {
    if (e.target.classList.contains('main')) {
        dragSrcEl = this;
        this.style.opacity = '0.4';
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.outerHTML);
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
    e.preventDefault();
    return false;
}

/**
 * Permet de mettre la classe `over` à l'élément `.draggable` lorsque l'élément `.draggable` est survolé
 * @param e
 */
function handleDragEnter(e) {
    let targetDraggable = e.target.closest('.draggable');
    if (targetDraggable) {
        targetDraggable.classList.add('over');
    }
}

/**
 * Permet de retirer la classe `over` à l'élément `.draggable` lorsque l'élément `.draggable` n'est plus survolé
 * @param e
 */
function handleDragLeave(e) {
    let targetDraggable = e.target.closest('.draggable');
    if (!targetDraggable || !targetDraggable.contains(e.relatedTarget)) {
        targetDraggable.classList.remove('over');
    }
}

/**
 * Permet d'échanger le contenu de deux éléments `.draggable`
 * @param e
 */
function handleDrop(e) {
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
        updateDraggables();
    }
}

/**
 * Permet de retirer la classe `over` à l'élément `.draggable` lorsque l'élément `.draggable` n'est plus survolé
 * @param e
 */
function handleDragEnd(e) {
    console.log('handleDragEnd');
    document.querySelectorAll('.draggable').forEach(el => {
        el.classList.remove('over');
        el.style.opacity = '1';
    });
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

//les div.card sont drag & droppables, elles peuvent être déplacées dans les div.stockage pour s'échanger avec d'autres cartes
let cards = document.querySelectorAll('.card');
let stockages = document.querySelectorAll('.stockage');

cards.forEach(card => {
    card.setAttribute('draggable', 'true');
    card.addEventListener('dragstart', dragStart);
    card.addEventListener('dragend', dragEnd);
});

stockages.forEach(stockage => {
    stockage.addEventListener('dragover', dragOver);
    stockage.addEventListener('dragenter', dragEnter);
    stockage.addEventListener('dragleave', dragLeave);
    stockage.addEventListener('drop', dragDrop);

});

let draggedCard = null;

function dragStart(e) {
    console.log('dragStart');
    draggedCard = this;
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/html', this.outerHTML);
}

function dragEnd() {
    console.log('dragEnd');
    draggedCard = null;
}

function dragOver(e) {
    console.log('dragOver');
    e.preventDefault();
}

function dragEnter(e) {
    console.log('dragEnter');
    if (draggedCard) {
        let targetStockage = e.target.closest('.stockage');
        if (targetStockage) {
            targetStockage.classList.add('over');
        }
    }
}

function dragLeave(e) {
    console.log('dragLeave');
    if (draggedCard) {
        let targetStockage = e.target.closest('.stockage');
        if (targetStockage) {
            targetStockage.classList.remove('over');
        }
    }
}


function dragDrop(e) {
    console.log('dragDrop');
    e.preventDefault();
    this.classList.remove('over');
    if (draggedCard) {
        this.appendChild(draggedCard);
    }
}


