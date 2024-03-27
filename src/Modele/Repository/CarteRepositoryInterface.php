<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\Carte;

interface CarteRepositoryInterface
{
    public function recupererCartesColonne(int $idcolonne): array;

    public function recupererCartesTableau(int $idTableau): array;

    /**
     * @return Carte[]
     */
    public function recupererCartesUtilisateur(string $login): array;

    public function getNombreCartesTotalUtilisateur(string $login): int;

    public function getNextIdCarte(): int;

    public function getAffectationsCarte(Carte $carte): ?array;

    public function setAffectationsCarte(?array $affectationsCarte, Carte $carte): void;

    public function getAllFromCartes(int $idCarte): array;
}