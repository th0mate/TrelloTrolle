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
     * @throws ServiceException
     */
    public function recupererColonne(int $idColonne): Colonne;

    public function recupererColonnesTableau($idTableau): array;

    /**
     * @throws TableauException
     */
    public function supprimerColonne(Tableau $tableau, $idColonne): array;

    /**
     * @throws CreationException
     */
    public function isSetNomColonne($nomColonne): void;

    /**
     * @throws CreationException
     * @throws ServiceException
     */
    public function recupererColonneAndNomColonne($idColonne, $nomColonne): Colonne;

    public function creerColonne(Tableau $tableau, $nomColonne): Colonne;

    public function miseAJourColonne(Colonne $colonne): Colonne;
    public function getNextIdColonne(): int;
    public function inverserOrdreColonnes($idColonne1, $idColonne2): void;

}