<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;

interface TableauRepositoryInterface
{
    /**
     * @param string $login
     * @return array
     */
    public function recupererTableauxUtilisateur(string $login): array;

    /**
     * @param string $codeTableau
     * @return AbstractDataObject|null
     */
    public function recupererParCodeTableau(string $codeTableau): ?AbstractDataObject;

    /**
     * @return Tableau[]
     */
    public function recupererTableauxOuUtilisateurEstMembre(string $login): array;

    /**
     * @return Tableau[]
     */
    public function recupererTableauxParticipeUtilisateur(string $login): array;

    /**
     * @return int
     */
    public function getNextIdTableau(): int;

    /**
     * @param string $login
     * @return int
     */
    public function getNombreTableauxTotalUtilisateur(string $login): int;

    /**
     * @param string $login
     * @param Tableau $tableau
     * @return bool
     */
    public function estParticipant(string $login, Tableau $tableau): bool;

    /**
     * @param $login
     * @param Tableau $tableau
     * @return bool
     */
    public function estProprietaire($login, Tableau $tableau): bool;

    /**
     * @param string $login
     * @param Tableau $tableau
     * @return bool
     */
    public function estParticipantOuProprietaire(string $login, Tableau $tableau): bool;

    /**
     * @param Tableau $tableau
     * @return array|null
     */
    public function getParticipants(Tableau $tableau): ?array;

    /**
     * @param array|null $participants
     * @param Tableau $tableau
     * @return void
     */
    public function setParticipants(?array $participants, Tableau $tableau): void;

    /**
     * @param Tableau $tableau
     * @return Utilisateur
     */
    public function getProprietaire(Tableau $tableau) : Utilisateur;

    /**
     * @param int $idTableau
     * @return array
     */
    public function getAllFromTableau(int $idTableau): array;

}