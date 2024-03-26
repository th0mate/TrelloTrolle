import {applyAndRegister, reactive, startReactiveDom} from "./reactive.js";

let formulaireModificationColonne = reactive({
    titre:"",
    idColonne:"",


}, "formulaireModificationColonne");

applyAndRegister({});

startReactiveDom();