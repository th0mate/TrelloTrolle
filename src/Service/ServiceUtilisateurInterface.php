<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Service\Exception\MiseAJourException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;

interface ServiceUtilisateurInterface
{
    /**
     * @throws TableauException
     */
    public function estParticipant(Tableau $tableau,$loginConnecte): void;

    public function recupererUtilisateurParCle($login): ?AbstractDataObject;

    /**
     * @throws TableauException
     */
    public function estProprietaire(Tableau $tableau, $login): void;

    /**
     * @throws TableauException
     */
    public function isNotNullLogin($login, Tableau $tableau, $action): void;

    /**
     * @throws TableauException
     */
    public function utilisateurExistant($login, Tableau $tableau): AbstractDataObject;

    /**
     * @throws TableauException
     */
    public function ajouterMembre(Tableau $tableau, mixed $membresAAjouter,$loginConnecte): void;

    /**
     * @throws TableauException
     */
    public function supprimerMembre(Tableau $tableau, $login,$loginConnecte): AbstractDataObject;

    /**
     * @throws ServiceException
     */
    public function recupererCompte(String $mail): void;

    /**
     * @throws TableauException
     */
    public function verificationsMembre(Tableau $tableau, $login): array;

    /**
     * @throws MiseAJourException
     */
    public function mettreAJourUtilisateur($attributs): void;

    /**
     * @throws ServiceException
     */
    public function supprimerUtilisateur($login): void;

    /**
     * @throws ServiceException
     * @throws \Exception
     */
    public function creerUtilisateur($attributs): void;

    public function rechercheUtilisateur(?string $recherche): array;
    public function getParticipants(Tableau $tableau): ?array;

    public function getProprietaireTableau(Tableau $tableau): Utilisateur;
    public function recupererAffectationsColonne($colonne, $login);

}