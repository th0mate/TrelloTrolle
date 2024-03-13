let objectByName = new Map();
let registeringEffect = null;
let objetDependencies = new Map();

function applyAndRegister(effect) {
    registeringEffect = effect;
    effect();
    registeringEffect = null;
}

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

function trigger(target, key) {
    if (objetDependencies.get(target).has(key)) {
        for (let effect of objetDependencies.get(target).get(key)) {
            effect();
        }
    }
}

function registerEffect(target, key) {
    if (!objetDependencies.get(target).has(key)) {
        objetDependencies.get(target).set(key, new Set());

    }
    objetDependencies.get(target).get(key).add(registeringEffect);
}

function startReactiveDom() {
    for (let elementClickable of document.querySelectorAll("[data-onclick]")) {
        const [nomObjet, methode, argument] = elementClickable.dataset.onclick.split(/[.()]+/);
        elementClickable.addEventListener('click', (event) => {
            const objet = objectByName.get(nomObjet);
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