let l = new WeakMap;
let c = new Map;
let o = null;

function f(t) {
    o = t;
    t();
    o = null
}

function a(t, e) {
    if (l.get(t).get(e) === undefined) l.get(t).set(e, new Set);
    l.get(t).get(e).add(o)
}

function r(e, n) {
    if (l.get(e).get(n) === undefined) return;
    for (let t of l.get(e).get(n)) t()
}

function t(t, e) {
    l.set(t, new Map);
    const n = {
        get(t, e, n) {
            if (o !== null) a(t, e);
            return Reflect.get(t, e, n)
        }, set(t, e, n, l) {
            const o = Reflect.set(t, e, n, l);
            r(t, e);
            return o
        }
    };
    c.set(e, new Proxy(t, n));
    return c.get(e)
}

function u(e = document) {
    for (let t of e.querySelectorAll("input.reactiveInput")) {
        const [o, a] = t.dataset.inputvar.split(".");
        t.addEventListener("change", t => {
            c.get(o)[a] = t.target.value
        })
    }
    for (let t of e.querySelectorAll("[data-onclick]")) {
        const [o, r, s] = t.dataset.onclick.split(/[.()]+/);
        t.addEventListener("click", t => {
            c.get(o)[r](s)
        })
    }
    for (let t of e.querySelectorAll("[data-textvar]")) {
        const [o, a] = t.dataset.textvar.split(".");
        f(() => {
            t.textContent = c.get(o)[a]
        })
    }
    for (let t of e.querySelectorAll("[data-textfun]")) {
        const [o, r, s] = t.dataset.textfun.split(/[.()]+/);
        f(() => {
            t.textContent = c.get(o)[r](s)
        })
    }
    for (let t of e.querySelectorAll("[data-stylefun]")) {
        const [o, r, s] = t.dataset.stylefun.split(/[.()]+/);
        f(() => {
            Object.assign(t.style, c.get(o)[r](s))
        })
    }
    const n = e.querySelectorAll("[data-htmlvar]");
    const l = e.querySelectorAll("[data-htmlfun]");
    for (let t of n) {
        const [o, a] = t.dataset.htmlvar.split(".");
        f(() => {
            t.innerHTML = c.get(o)[a];
            u(t)
        })
    }
    for (let t of l) {
        const [o, r, s] = t.dataset.htmlfun.split(/[.()]+/);
        f(() => {
            t.innerHTML = c.get(o)[r](s);
            u(t)
        })
    }
}

export {f as applyAndRegister, t as reactive, u as startReactiveDom};