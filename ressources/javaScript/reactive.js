/**
 * Enregistre les objets réactifs par leur nom et applique leurs effets, en utilisant un Proxy et des écouteurs d'événements
 * @type {Map<any, any>} objectByName un Map qui associe un nom à un objet réactif
 * @type {null} registeringEffect l'effet en cours d'enregistrement
 * @type {Map<any, any>} objetDependencies un Map qui associe un objet à un Map qui associe une clé à un Set d'effets
 */
let objectByName = new Map();
let registeringEffect = null;
let objetDependencies = new Map();


/**
 * Applique l'effet et l'enregistre pour le reactiveDom
 * @param effect une fonction qui modifie des objets réactifs
 */
function applyAndRegister(effect) {
    registeringEffect = effect;
    if (typeof effect === 'function') {
        effect();
    }
    registeringEffect = null;
}

/**
 * Renvoie un objet réactif
 * @param passiveObject l'objet passif
 * @param name le nom de l'objet réactif
 * @returns {*|object|boolean} l'objet réactif
 */
function reactive(passiveObject, name) {
    objetDependencies.set(passiveObject, new Map());
    const handler = {
        get(target, key) {
            if (registeringEffect !== null)
                registerEffect(target, key);
            return target[key];
        },
        set(target, key, value) {
            target[key] = value;
            trigger(target, key);
            return true;
        },
    };

    const reactiveObject = new Proxy(passiveObject, handler);
    objectByName.set(name, reactiveObject);
    return reactiveObject;
}

/**
 * Déclenche les effets enregistrés pour un objet et une clé donnés
 * @param target l'objet
 * @param key la clé
 */
function trigger(target, key) {
    if (objetDependencies.get(target).has(key)) {
        for (let effect of objetDependencies.get(target).get(key)) {
            if (effect !== null && effect !== undefined) {
                effect();
            }
        }
    }
}

/**
 * Enregistre un effet pour un objet et une clé donnés
 * @param target l'objet
 * @param key la clé
 */
function registerEffect(target, key) {
    if (!objetDependencies.get(target).has(key)) {
        objetDependencies.get(target).set(key, new Set());
    }
    if (typeof registeringEffect === 'function') {
        objetDependencies.get(target).get(key).add(registeringEffect);
    }
}

/**
 * Démarre le reactiveDom en ajoutant les écouteurs d'événements et en appliquant les effets
 */
function startReactiveDom(subDom = document) {

    /**
     * Pour le clic gauche sur un élément
     */
    for (let elementClickable of document.querySelectorAll("[data-onclick]")) {
        const [nomObjet, methode, argument] = elementClickable.dataset.onclick.split(/[.()]+/);
        elementClickable.addEventListener('click', (event) => {
            const objet = objectByName.get(nomObjet);
            if (objet[methode] !== undefined) {
                objet[methode](argument);
            }
        });
        elementClickable.removeAttribute('data-onclick');
    }

    /**
     * Pour le contenu texte d'un élément
     */
    for (let rel of document.querySelectorAll("[data-textfun]")) {
        const [obj, fun, arg] = rel.dataset.textfun.split(/[.()]+/);
        applyAndRegister(() => {
            rel.textContent = objectByName.get(obj)[fun](arg)
        });
    }

    /**
     * Pour le contenu texte d'un élément
     */
    for (let rel of document.querySelectorAll("[data-textvar]")) {
        const [obj, prop] = rel.dataset.textvar.split('.');
        applyAndRegister(() => {
            rel.textContent = objectByName.get(obj)[prop]
        });
    }

    /**
     * Pour le style d'un élément
     */
    for (let rel of document.querySelectorAll("[data-stylefun]")) {
        const [obj, fun, arg] = rel.dataset.stylefun.split(/[.()]+/);
        applyAndRegister(() => {
            const style = objectByName.get(obj)[fun](arg);
            for (let prop in style) {
                rel.style[prop] = style[prop];
            }
        });
    }

    /**
     * Pour le contenu des inputs
     */
    for (let rel of document.querySelectorAll("[data-reactiveInput]")) {
        const [obj, prop] = rel.dataset.reactiveinput.split('.');
        rel.addEventListener('input', (event) => {
            objectByName.get(obj)[prop] = event.target.value;
        });
    }

    /**
     * Pour le contenu html d'un élément
     */
    for (let rel of subDom.querySelectorAll("[data-htmlfun]")) {
        const [obj, fun, arg] = rel.dataset.htmlfun.split(/[.()]+/);
        applyAndRegister(() => {
            let reactiveObject = objectByName.get(obj);
            if (reactiveObject !== undefined && reactiveObject !== null && typeof reactiveObject[fun] === 'function') {
                rel.innerHTML = reactiveObject[fun](arg);
            }
            rel.removeAttribute('data-htmlfun');
        });
    }

    /**
     * Lors du cochage d'une checkbox
     */
    for (let rel of document.querySelectorAll("[data-oncheck]")) {
        const [obj, fun, arg] = rel.dataset.oncheck.split(/[.()]+/);
        rel.addEventListener('change', (event) => {
            if (event.target.checked) {
                objectByName.get(obj)[fun](arg);
            }
        });
    }

    /**
     * Lors du décochage d'une checkbox
     */
    for (let rel of document.querySelectorAll("[data-onUncheck]")) {
        const [obj, fun, arg] = rel.dataset.onuncheck.split(/[.()]+/);
        rel.addEventListener('change', (event) => {
            if (!event.target.checked) {
                objectByName.get(obj)[fun](arg);
            }
        });
    }

    /**
     * Au changement fait dans un élément input
     */
    for (let rel of document.querySelectorAll("[data-onChange]")) {
        const [obj, fun, arg] = rel.dataset.onchange.split(/[.()]+/);
        rel.addEventListener('input', (event) => {
            objectByName.get(obj)[fun](arg);
        });
        rel.removeAttribute('data-onChange');
    }

    /**
     * Lors de l'appui sur la touche entrée dans un input
     */
    for (let rel of document.querySelectorAll("[data-onEnter]")) {
        const [obj, fun, arg] = rel.dataset.onenter.split(/[.()]+/);
        rel.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                objectByName.get(obj)[fun](arg);
            }
        });
        rel.removeAttribute('data-onEnter');
    }

    /**
     * Au chargement de l'élément dans la page
     */
    for (let rel of document.querySelectorAll("[data-onload]")) {
        const [obj, fun, arg] = rel.dataset.onload.split(/[.()]+/);
        if (objectByName.get(obj) !== undefined) {
            objectByName.get(obj)[fun](arg);
            rel.removeAttribute('data-onload');
        }
    }

    /**
     * Au survol avec la souris de l'élément
     */
    for (let rel of document.querySelectorAll("[data-onhover]")) {
        const [obj, fun, arg] = rel.dataset.onhover.split(/[.()]+/);
        rel.addEventListener('mouseenter', (event) => {
            if (objectByName.get(obj) !== undefined) {
                objectByName.get(obj)[fun](arg);
                rel.removeAttribute('data-onhover');
            }
        });
    }

    /**
     * Lorsque la souris quitte le survol de l'élément
     */
    for (let rel of document.querySelectorAll("[data-onleave]")) {
        const [obj, fun, arg] = rel.dataset.onleave.split(/[.()]+/);
        rel.addEventListener('mouseleave', (event) => {
            if (objectByName.get(obj) !== undefined) {
                objectByName.get(obj)[fun](arg);
                rel.removeAttribute('data-onleave');
            }
        });
    }

    /**
     * Au clic droit sur l'élément
     */
    for (let rel of document.querySelectorAll("[data-onrightclick]")) {
        const [obj, fun, arg] = rel.dataset.onrightclick.split(/[.()]+/);
        rel.addEventListener('contextmenu', (event) => {
            event.preventDefault();
            objectByName.get(obj)[fun](arg);
        });
        rel.removeAttribute('data-onrightclick');
    }
}

window.startReactiveDom = startReactiveDom;

export {applyAndRegister, reactive, startReactiveDom, objectByName};