/**
 * Fonction qui crée un message flash
 * @param message{string} : le message à afficher
 * @param type{string} : le type de message (success, danger, warning, info)
 * @returns {HTMLDivElement} : le message flash
 */
function creerMessageFlash(message, type) {
    var div = document.createElement('div');
    div.className = 'alert alert-' + type;
    var img = document.createElement('img');
    //la source de l'image est une variable créée dans le html, ayant le même nom que le type
    img.src = window[type];
    img.alt = type;
    var txt = document.createElement('p');
    txt.innerHTML = message;
    div.appendChild(img);
    div.appendChild(txt);
    div.id = 'flash';
    div.style.top = '-100px';

    return div;
}

/**
 * Fonction qui affiche un message flash
 * @param message{string} : le message à afficher
 * @param type{string} : le type de message (success, danger, warning, info)
 */
function afficherMessageFlash(message, type) {
    var flash = creerMessageFlash(message, type);
    document.body.appendChild(flash);

    setTimeout(() => animateDown(flash), 100);
    flash.addEventListener('click', () => {
        animateUp(flash);
    });
}

/**
 * Crée une animation pour l'affichage de messages flash
 * @param element{element} : l'élément à animer
 */
function animateDown(element) {
    element.style.transition = 'top 1s ease-in-out';
    element.style.top = '50px';
    setTimeout(() => {
        animateUp(element);
    }, 5000);
}

/**
 * Crée une animation pour la disparition de messages flash
 * @param element{element} : l'élément à animer
 */
function animateUp(element) {
    element.style.top = '-100px';
    //on supprime l'élément du DOM après l'animation
    setTimeout(() => {
        document.body.removeChild(element);
    }, 1000);
}
