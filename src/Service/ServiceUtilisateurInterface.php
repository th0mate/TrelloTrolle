<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Service\Exception\MiseAJourException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;
use PHPUnit\Exception;

interface ServiceUtilisateurInterface
{

    /**
     * @param Tableau $tableau
     * @param $loginConnecte
     * @return void
     */
    public function estParticipant(Tableau $tableau, $loginConnecte): void;

    /**
     * @param $login
     * @return AbstractDataObject|null
     */
    public function recupererUtilisateurParCle($login): ?AbstractDataObject;


    /**
     * @param Tableau $tableau
     * @param $login
     * @return void
     */
    public function estProprietaire(Tableau $tableau, $login): void;


    /**
     * @param $login
     * @param Tableau $tableau
     * @param $action
     * @return void
     */
    public function isNotNullLogin($login, Tableau $tableau, $action): void;


    /**
     * @param $login
     * @param Tableau $tableau
     * @return AbstractDataObject
     */
    public function utilisateurExistant($login, Tableau $tableau): AbstractDataObject;


    /**
     * @param Tableau $tableau
     * @param mixed $login
     * @param $loginConnecte
     * @return void
     */

    public function ajouterMembre(Tableau $tableau, mixed $membresAAjouter,$loginConnecte): void;

    /**
     * @param Tableau $tableau
     * @param $login
     * @param $loginConnecte
     * @return AbstractDataObject
     */
    public function supprimerMembre(Tableau $tableau, $login, $loginConnecte): AbstractDataObject;


    /**
     * @param $mail
     * @return array
     */
    public function recupererCompte($mail): array;


    /**
     * @param Tableau $tableau
     * @param $login
     * @return array
     * @throws TableauException
     */
    public function verificationsMembre(Tableau $tableau, $login): array;


    /**
     * @param $attributs
     * @return void
     * @throws MiseAJourException
     */
    public function mettreAJourUtilisateur($attributs): void;


    /**
     * @param $login
     * @return void
     * @throws ServiceException
     */
    public function supprimerUtilisateur($login): void;


    /**
     * @param $attributs
     * @return void
     * @throws ServiceException
     * @throws \Exception
     */
    public function creerUtilisateur($attributs): void;

    /**
     * @param string|null $recherche
     * @return array
     */
    public function rechercheUtilisateur(?string $recherche): array;

    /**
     * @param Tableau $tableau
     * @return array|null
     */
    public function getParticipants(Tableau $tableau): ?array;

    /**
     * @param Tableau $tableau
     * @return Utilisateur
     */
    public function getProprietaireTableau(Tableau $tableau): Utilisateur;
    public function recupererAffectationsColonne($colonne, $login);

}