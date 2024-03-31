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
     * @param $idColonne
     * @return Colonne
     */
    public function recupererColonne(int $idColonne): Colonne;

    /**
     * @param $idTableau
     * @return array
     */
    public function recupererColonnesTableau($idTableau): array;


    /**
     * @param Tableau $tableau
     * @param $idColonne
     * @return array
     */
    public function supprimerColonne(Tableau $tableau, $idColonne): array;


    /**
     * @param $nomColonne
     * @return void
     */
    public function isSetNomColonne($nomColonne): void;


    /**
     * @param $idColonne
     * @param $nomColonne
     * @return Colonne
     */
    public function recupererColonneAndNomColonne($idColonne, $nomColonne): Colonne;

    /**
     * @param Tableau $tableau
     * @param $nomColonne
     * @return Colonne
     */
    public function creerColonne(Tableau $tableau, $nomColonne): Colonne;

    /**
     * @param Colonne $colonne
     * @return Colonne
     */
    public function miseAJourColonne(Colonne $colonne): Colonne;

    /**
     * @return int
     */
    public function getNextIdColonne(): int;

    /**
     * @param $idColonne1
     * @param $idColonne2
     * @return void
     */
    public function inverserOrdreColonnes($idColonne1, $idColonne2): void;

}