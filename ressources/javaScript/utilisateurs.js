import {applyAndRegister, reactive, startReactiveDom} from "./reactive.js";

let utilisateurs = reactive({
    prenom: "",
    nom: "",
    login: "",
    colonnes: [],
    drapeau: false,

    creerUtilisateurDepuisBaseDeDonnees: async function () {

        if (this.drapeau) {
            return;
        }

        this.drapeau = true;

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
            let membres = await membresTableau.json();
            let proprio = await proprioTableau.json();
            const tousMembres = membres.concat(proprio);

            console.log(JSON.stringify(tousMembres, null, 2));

            this.drapeau = false;
        }
    },


}, "utilisateur");

applyAndRegister({});

startReactiveDom();


