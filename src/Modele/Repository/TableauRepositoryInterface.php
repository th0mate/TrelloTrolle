<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Tableau;

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

    public function participants(): array;

    public function estParticipant(string $login): bool;

    public function estProprietaire($login): bool;

    public function estParticipantOuProprietaire(string $login): bool;

    public function getParticipants(Tableau $idcle): ?array;

    public function setParticipants(?array $participants, Tableau $instance): void;
}