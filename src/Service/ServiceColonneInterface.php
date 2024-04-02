<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Service\Exception\CreationException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;

interface ServiceColonneInterface
{

    /**
     * Fonction permettant de récupérer une colonne par son id
     * @param $idColonne L'id de la colonne à récupérer
     * @return Colonne La colonne récupérée
     */
    public function recupererColonne(int $idColonne): Colonne;

    /**
     * Fonction permettant de récupérer les colonnes d'un tableau
     * @param $idTableau L'id du tableau dont on veut récupérer les colonnes
     * @return array Les colonnes du tableau
     */
    public function recupererColonnesTableau($idTableau): array;


    /**
     * Fonction permettant de supprimer une colonne d'un tableau
     * @param Tableau $tableau Le tableau dont on veut supprimer la colonne
     * @param $idColonne L'id de la colonne à supprimer
     * @return array Les colonnes du tableau après suppression de la colonne donnée
     */
    public function supprimerColonne(Tableau $tableau, $idColonne): array;


    /**
     * Fonction permettant de vérifier si le nom de la colonne est assigné
     * @param $nomColonne, Le nom de la colonne à vérifier
     * @return void
     */
    public function isSetNomColonne($nomColonne): void;


    /**
     * Fonction permettant de récupérer une colonne par son id et son nom
     * @param $idColonne L'id de la colonne à récupérer
     * @param $nomColonne, Le nom de la colonne à récupérer
     * @return Colonne La colonne récupérée
     */
    public function recupererColonneAndNomColonne($idColonne, $nomColonne): Colonne;

    /**
     * Fonction permettant de créer une colonne
     * @param Tableau $tableau Le tableau sur lequel on veut créer la colonne
     * @param $nomColonne, Le nom de la colonne à créer
     * @return Colonne La colonne créée
     */
    public function creerColonne(Tableau $tableau, $nomColonne): Colonne;

    /**
     * Fonction permettant de mettre à jour une colonne
     * @param Colonne $colonne La colonne à mettre à jour
     * @return Colonne La colonne mise à jour
     */
    public function miseAJourColonne(Colonne $colonne): Colonne;

    /**
     * Fonction permettant de récupérer l'id de la colonne suivante
     * @return int L'id de la colonne suivante
     */
    public function getNextIdColonne(): int;

    /**
     * Fonction permettant d'inverser l'ordre de deux colonnes
     * @param $idColonne1, L'id de la première colonne
     * @param $idColonne2 , L'id de la deuxième colonne
     * @return void
     */
    public function inverserOrdreColonnes(int $idColonne1, int $idColonne2): void;


}