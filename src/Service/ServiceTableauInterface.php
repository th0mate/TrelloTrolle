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
     * @param $idTableau
     * @return Tableau
     */
    public function recupererTableauParId($idTableau): Tableau;


    /**
     * @param $codeTableau
     * @return Tableau
     */
    public function recupererTableauParCode($codeTableau): Tableau;

    /**
     * @param Tableau $tableau
     * @return array
     */
    public function recupererCartesColonnes(Tableau $tableau): array;

    /**
     * @param $login
     * @return array
     */
    public function recupererTableauEstMembre($login): array;


    /**
     * @param $nomTableau
     * @param Tableau $tableau
     * @return void
     */
    public function isNotNullNomTableau($nomTableau, Tableau $tableau): void;

    /**
     * @param Tableau $tableau
     * @return void
     */
    public function mettreAJourTableau(Tableau $tableau): void;


    /**
     * @param $idTableau
     * @return void
     * @throws ServiceException
     */
    public function supprimerTableau($idTableau): void;


    /**
     * @param Tableau $tableau
     * @param AbstractDataObject $utilisateur
     * @return void
     * @throws ServiceException
     */
    public function quitterTableau(Tableau $tableau, AbstractDataObject $utilisateur): void;


    /**
     * @param $nomTableau
     * @param $login
     * @return mixed
     * @throws ServiceException
     */
    public function creerTableau($nomTableau, $login);


    /**
     * @param Tableau $tableau
     * @param $login
     * @return mixed
     */
    public function estParticipant(Tableau $tableau, $login);
}