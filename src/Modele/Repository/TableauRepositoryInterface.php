<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;

interface TableauRepositoryInterface
{
    public function recupererTableauxUtilisateur(string $login): array;

    public function recupererParCodeTableau(string $codeTableau): ?AbstractDataObject;

    /**
     * @return Tableau[]
     */
    public function recupererTableauxOuUtilisateurEstMembre(string $login): array;

    /**
     * @return Tableau[]
     */
    public function recupererTableauxParticipeUtilisateur(string $login): array;

    public function getNextIdTableau(): int;

    public function getNombreTableauxTotalUtilisateur(string $login): int;

    public function estParticipant(string $login, Tableau $tableau): bool;

    public function estProprietaire($login, Tableau $tableau): bool;

    public function estParticipantOuProprietaire(string $login, Tableau $tableau): bool;

    public function getParticipants(Tableau $tableau): ?array;

    public function setParticipants(?array $participants, Tableau $tableau): void;

    public function getProprietaire(Tableau $tableau) : Utilisateur;

    public function getAllFromTableau(int $idTableau): Tableau;

}