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

    public function getAffectationsCarte(Carte $idcle): ?array;

    public function setAffectationsCarte(?array $affectationsCarte, Carte $instance): void;
}