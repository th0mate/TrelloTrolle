import {applyAndRegister, reactive, startReactiveDom} from "./reactive.js";

let tableauxDatas = reactive({
    tableaux: [],
    addTableau: function (tableau) {
        this.tableaux.push(tableau);
    },
    removeTableau: function (index) {
            this.tableaux.splice(index, 1);
    },
    getTableau: function (index) {
        return this.tableaux[index];
    },
    getTableaux: function () {
        return this.tableaux;
    }
}, "tableauxDatas");

applyAndRegister(() => {
    let tableaux = tableauxDatas.getTableaux();
    let tableauxDiv = document.getElementById("tableaux");
    tableauxDiv.innerHTML = "";
    for (let i = 0; i < tableaux.length; i++) {
        let tableau = tableaux[i];
        let tableauDiv = document.createElement("div");
        tableauDiv.innerHTML = tableau;
        let removeButton = document.createElement("button");
        removeButton.textContent = "Remove";
        removeButton.addEventListener("click", () => {
            tableauxDatas.removeTableau(i);
        });
        tableauDiv.appendChild(removeButton);
        tableauxDiv.appendChild(tableauDiv);
    }
});

startReactiveDom();