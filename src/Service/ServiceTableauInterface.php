<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\AbstractRepository;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;

interface ServiceTableauInterface
{
    /**
     * @throws ServiceException
     */
    public function recupererTableauParId($idTableau): Tableau;

    /**
     * @throws ServiceException
     */
    public function recupererTableauParCode($codeTableau): Tableau;

    public function recupererCartesColonnes(Tableau $tableau): array;

    public function recupererTableauEstMembre($login): array;

    /**
     * @throws TableauException
     */
    public function isNotNullNomTableau($nomTableau, Tableau $tableau): void;

    public function mettreAJourTableau(Tableau $tableau): void;

    /**
     * @throws ServiceException
     */
    public function supprimerTableau($idTableau): void;

    /**
     * @throws ServiceException
     */
    public function quitterTableau(Tableau $tableau, AbstractDataObject $utilisateur): void;

    /**
     * @throws ServiceException
     */
    public function creerTableau($nomTableau,$login);

    public function estParticipant(Tableau $tableau,$login);
}