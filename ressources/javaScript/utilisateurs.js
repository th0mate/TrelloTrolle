import {applyAndRegister, reactive, startReactiveDom} from "./reactive.js";

let utilisateurs = reactive({
    prenom: "",
    nom: "",
    login: "",
    colonnes: [],


}, "utilisateur");

applyAndRegister({});

startReactiveDom();


async function creerUtilisateurDepuisBaseDeDonnees() {

    let membresTableau = await fetch(apiBase + '/tableau/membre/getPourTableau', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            idTableau: document.querySelector('.adder').getAttribute('data-tableau')
        })
    });

    let proprioTableau = await fetch(apiBase + '/tableau/membre/getProprio', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            idTableau: document.querySelector('.adder').getAttribute('data-tableau')
        })
    });

    if (membresTableau.status !== 200 || proprioTableau.status !== 200) {
        console.error("Erreur lors de la récupération des membres du tableau");
    } else {
        //TODO
    }


}

