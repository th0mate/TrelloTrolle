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

    public function recupererCartesColonnes($tableau): array;

    public function recupererTableauEstMembre($login);

    /**
     * @throws TableauException
     */
    public function isNotNullNomTableau($nomTableau, $tableau);

    public function mettreAJourTableau($tableau);

    /**
     * @throws ServiceException
     */
    public function supprimerTableau($idTableau);

    /**
     * @throws ServiceException
     */
    public function quitterTableau(Tableau $tableau, Utilisateur $utilisateur);

    /**
     * @throws ServiceException
     */
    public function creerTableau($nomTableau);

    public function estParticipant();
}