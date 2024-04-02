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
     * Cette fonction permet de vérifier si un utilisateur est un participant d'un tableau
     *
     * @param Tableau $tableau  Le tableau sur lequel on veut vérifier si l'utilisateur est participant
     * @param $loginConnecte, Le login de l'utilisateur connecté
     * @return void
     */
    public function estParticipant(Tableau $tableau, $loginConnecte): void;

    /**
     * Cette fonction permet de récupérer un utilisateur par sa clé
     * @param $login, La clé de l'utilisateur
     * @return AbstractDataObject|null L'utilisateur
     */
    public function recupererUtilisateurParCle($login): ?AbstractDataObject;


    /**
     * Cette fonction permet de vérifier si un utilisateur est le propriétaire d'un tableau
     * @param Tableau $tableau Le tableau sur lequel on veut vérifier si l'utilisateur est propriétaire
     * @param $login, La clé de l'utilisateur
     * @return void
     */
    public function estProprietaire(Tableau $tableau, $login): void;


    /**
     * Cette fonction permet de vérifier si un utilisateur n'a pas un login null
     * @param $login, Le login de l'utilisateur
     * @param Tableau $tableau Le tableau sur lequel on veut vérifier si l'utilisateur est propriétaire
     * @param $action L'action à réaliser
     * @return void
     */
    public function isNotNullLogin($login, Tableau $tableau, $action): void;


    /**
     * Cette fonction permet de vérifier si un utilisateur existe
     * @param $login, Le login de l'utilisateur
     * @param Tableau $tableau Le tableau sur lequel on veut vérifier si l'utilisateur est propriétaire
     * @return AbstractDataObject L'utilisateur
     */
    public function utilisateurExistant($login, Tableau $tableau): AbstractDataObject;


    /**
     * Cette fonction permet d'ajouter un membre à un tableau
     * @param Tableau $tableau Le tableau sur lequel on veut ajouter un membre
     * @param mixed $login Le login de l'utilisateur à ajouter
     * @param $loginConnecte, Le login de l'utilisateur connecté
     * @return void
     */

    public function ajouterMembre(Tableau $tableau, mixed $membresAAjouter,$loginConnecte): void;

    /**
     * Cette fonction permet de supprimer un membre d'un tableau
     * @param Tableau $tableau Le tableau sur lequel on veut supprimer un membre
     * @param $login, Le login de l'utilisateur à supprimer
     * @param $loginConnecte, Le login de l'utilisateur connecté
     * @return AbstractDataObject
     */
    public function supprimerMembre(Tableau $tableau, $login, $loginConnecte): AbstractDataObject;


    /**
     * Cette fonction permet de récupérer un compte utilisateur par son mail
     * @param $mail, Le mail de l'utilisateur
     * @return array Le compte utilisateur
     */
    public function recupererCompte(String $mail): void;


    /**
     * Cette fonction permet de vérifier si un utilisateur est un membre d'un tableau
     * @param Tableau $tableau Le tableau sur lequel on veut vérifier si l'utilisateur est membre
     * @param $login, Le login de l'utilisateur
     * @return array Les membres du tableau
     * @throws TableauException
     */
    public function verificationsMembre(Tableau $tableau, $login): array;


    /**
     * Cette fonction permet de mettre à jour un utilisateur
     * @param $attributs, Les nouveaux attributs de l'utilisateur
     * @return void
     * @throws MiseAJourException
     */
    public function mettreAJourUtilisateur($attributs): void;


    /**
     * Cette fonction permet de supprimer un utilisateur
     * @param $login, Le login de l'utilisateur à supprimer
     * @return void
     * @throws ServiceException
     */
    public function supprimerUtilisateur($login): void;


    /**
     * Cette fonction permet de créer un utilisateur
     * @param $attributs, Les attributs de l'utilisateur à créer
     * @return void
     * @throws ServiceException
     * @throws \Exception
     */
    public function creerUtilisateur($attributs): void;

    /**
     * Cette fonction permet de rechercher un utilisateur
     * @param string|null $recherche Le login de l'utilisateur à rechercher
     * @return array Les utilisateurs trouvés
     */
    public function rechercheUtilisateur(?string $recherche): array;

    /**
     * Cette fonction permet de récupérer les participants d'un tableau
     * @param Tableau $tableau Le tableau sur lequel on veut récupérer les participants
     * @return array|null Les participants du tableau
     */
    public function getParticipants(Tableau $tableau): ?array;

    /**
     * Cette fonction permet de récupérer le propriétaire d'un tableau
     * @param Tableau $tableau Le tableau sur lequel on veut récupérer le propriétaire
     * @return Utilisateur Le propriétaire du tableau
     */
    public function getProprietaireTableau(Tableau $tableau): Utilisateur;
    public function recupererAffectationsColonne($colonne, $login);

    public function verifNonce($login,$nonce): void;

    public function changerMotDePasse($login, $mdp, $mdp2): void;



}