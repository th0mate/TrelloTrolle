import {applyAndRegister, reactive, startReactiveDom} from "./reactive.js";

let tableauxDatas = reactive({

    supprimerCarte: function (idCarte) {
        return document.getElementById('c' + idCarte).remove();
    }

}, "tableauxDatas");

applyAndRegister(() => {
});

startReactiveDom();