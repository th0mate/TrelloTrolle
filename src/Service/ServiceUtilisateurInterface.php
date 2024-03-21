<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Service\Exception\MiseAJourException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;

interface ServiceUtilisateurInterface
{
    /**
     * @throws TableauException
     */
    public function estParticipant($tableau);

    public function recupererUtilisateurParCle($login): \App\Trellotrolle\Modele\DataObject\AbstractDataObject;

    /**
     * @throws TableauException
     */
    public function estProprietaire(Tableau $tableau, $login);

    /**
     * @throws TableauException
     */
    public function isNotNullLogin($login, $tableau, $action);

    /**
     * @throws TableauException
     */
    public function utilisateurExistant($login, $tableau);

    /**
     * @throws TableauException
     */
    public function ajouterMembre(Tableau $tableau, mixed $login);

    /**
     * @throws TableauException
     */
    public function supprimerMembre(Tableau $tableau, $login);

    /**
     * @throws ServiceException
     */
    public function recupererCompte($mail);

    /**
     * @throws TableauException
     */
    public function verificationsMembre(Tableau $tableau, $login);

    /**
     * @throws MiseAJourException
     */
    public function mettreAJourUtilisateur($attributs);

    /**
     * @throws ServiceException
     */
    public function supprimerUtilisateur($login);

    /**
     * @throws ServiceException
     * @throws \Exception
     */
    public function creerUtilisateur($attributs);
}