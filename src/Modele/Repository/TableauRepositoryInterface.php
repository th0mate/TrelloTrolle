<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;

interface TableauRepositoryInterface
{
    /**
     * Fonction permettant de récupérer tous les tableaux d'un utilisateur
     * @param string $login   Le login de l'utilisateur
     * @return array Les tableaux récupérés
     */
    public function recupererTableauxUtilisateur(string $login): array;

    /**
     * Fonction permettant de récupérer un tableau en fonction de son code
     * @param string $codeTableau Le code du tableau
     * @return AbstractDataObject|null Le tableau récupéré
     */
    public function recupererParCodeTableau(string $codeTableau): ?AbstractDataObject;

    /**
     * Fonction permettant de récupérer tous les tableaux où un utilisateur est membre
     * @return Tableau[]  Les tableaux récupérés
     */
    public function recupererTableauxOuUtilisateurEstMembre(string $login): array;

    /**
     * Fonction permettant de récupérer le prochain id de tableau
     * @return Tableau[] Les tableaux récupérés
     */
    public function recupererTableauxParticipeUtilisateur(string $login): array;

    /**
     * Fonction permettant de récupérer le prochain id de tableau
     * @return int L'id du prochain tableau
     */
    public function getNextIdTableau(): int;

    /**
     * Fonction permettant de récupérer le nombre de tableaux total d'un utilisateur
     * @param string $login Le login de l'utilisateur
     * @return int Le nombre de tableaux total de l'utilisateur
     */
    public function getNombreTableauxTotalUtilisateur(string $login): int;

    /**
     * Fonction permettant de vérifier si un utilisateur est participant à un tableau
     * @param string $login Le login de l'utilisateur
     * @param Tableau $tableau Le tableau
     * @return bool Vrai si l'utilisateur est participant, faux sinon
     */
    public function estParticipant(string $login, Tableau $tableau): bool;

    /**
     * Fonction permettant de vérifier si un utilisateur est propriétaire d'un tableau
     * @param $login ,Le login de l'utilisateur
     * @param Tableau $tableau Le tableau
     * @return bool Vrai si l'utilisateur est propriétaire, faux sinon
     */
    public function estProprietaire($login, Tableau $tableau): bool;

    /**
     * Fonction permettant de vérifier si un utilisateur est participant ou propriétaire d'un tableau
     * @param string $login Le login de l'utilisateur
     * @param Tableau $tableau Le tableau
     * @return bool Vrai si l'utilisateur est participant ou propriétaire, faux sinon
     */
    public function estParticipantOuProprietaire(string $login, Tableau $tableau): bool;

    /**
     * Fonction permettant de récupérer les participants d'un tableau
     * @param Tableau $tableau Le tableau
     * @return array|null Les participants du tableau
     */
    public function getParticipants(Tableau $tableau): ?array;

    /**
     * Fonction permettant de mettre à jour les participants d'un tableau
     * @param array|null $participants Les participants du tableau
     * @param Tableau $tableau Le tableau
     * @return void
     */
    public function setParticipants(?array $participants, Tableau $tableau): void;

    /**
     * Fonction permettant de récupérer le propriétaire d'un tableau
     * @param Tableau $tableau Le tableau
     * @return Utilisateur Le propriétaire du tableau
     */
    public function getProprietaire(Tableau $tableau) : Utilisateur;


}