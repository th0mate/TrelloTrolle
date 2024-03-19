import {applyAndRegister, reactive, startReactiveDom} from './reactive.js';

let TableauxDatas = reactive({
    titreTableau: '',
    colonnes: [],
    cartes: [],
    participants: [],

}, 'tableau');

applyAndRegister(() => {
});

startReactiveDom();