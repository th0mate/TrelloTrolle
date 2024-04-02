<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\Colonne;

interface ColonneRepositoryInterface
{
    /**
     * Fonction permettant de récupérer toutes les colonnes d'un tableau
     * @param int $idTableau L'id du tableau
     * @return array La liste des colonnes du tableau
     */
    public function recupererColonnesTableau(int $idTableau): ?array;

    /**
     * Fonction permettant de récupérer le prochain id de colonne
     * @return int L'id de la prochaine colonne
     */
    public function getNextIdColonne(): int;

    /**
     * Fonction permettant de récupérer le nombre de colonnes total d'un tableau
     * @param int $idTableau L'id du tableau
     * @return int Le nombre de colonnes total du tableau
     */
    public function getNombreColonnesTotalTableau(int $idTableau): int;

    /**
     * Fonction permettant d'inverser l'ordre de deux colonnes
     * @param int $idColonne1 Id de la première colonne
     * @param int $idColonne2 Id de la deuxième colonne
     * @return void
     */
    public function inverserOrdreColonnes(int $idColonne1, int $idColonne2): void;


}