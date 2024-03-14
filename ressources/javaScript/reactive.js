/**
 * Enregistre les objets réactifs par leur nom et applique leurs effets, en utilisant un Proxy et des écouteurs d'événements
 * @type {Map<any, any>} objectByName un Map qui associe un nom à un objet réactif
 * @type {null} registeringEffect l'effet en cours d'enregistrement
 * @type {Map<any, any>} objetDependencies un Map qui associe un objet à un Map qui associe une clé à un Set d'effets
 *
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
    console.log(effect);
    effect();
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
            } else {
                console.error("Aucun effet pour " + target + " et " + key);
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
    objetDependencies.get(target).get(key).add(registeringEffect);
}

/**
 * Démarre le reactiveDom en ajoutant les écouteurs d'événements et en appliquant les effets
 */
function startReactiveDom(subDom = document) {
    for (let elementClickable of document.querySelectorAll("[data-onclick]")) {
        const [nomObjet, methode, argument] = elementClickable.dataset.onclick.split(/[.()]+/);
        elementClickable.addEventListener('click', (event) => {
            const objet = objectByName.get(nomObjet);
            console.log(objet, methode, argument);
            objet[methode](argument);
        })
    }
    for (let rel of document.querySelectorAll("[data-textfun]")) {
        const [obj, fun, arg] = rel.dataset.textfun.split(/[.()]+/);
        applyAndRegister(() => {
            rel.textContent = objectByName.get(obj)[fun](arg)
        });
    }
    for (let rel of document.querySelectorAll("[data-textvar]")) {
        const [obj, prop] = rel.dataset.textvar.split('.');
        applyAndRegister(() => {
            rel.textContent = objectByName.get(obj)[prop]
        });
    }
    for (let rel of document.querySelectorAll("[data-stylefun]")) {
        const [obj, fun, arg] = rel.dataset.stylefun.split(/[.()]+/);
        applyAndRegister(() => {
            const style = objectByName.get(obj)[fun](arg);
            for (let prop in style) {
                rel.style[prop] = style[prop];
            }
        });
    }
}

export {applyAndRegister, reactive, startReactiveDom};