<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\Colonne;

interface ColonneRepositoryInterface
{
    public function recupererColonnesTableau(int $idTableau): array;

    public function getNextIdColonne(): int;

    public function getNombreColonnesTotalTableau(int $idTableau): int;

    public function inverserOrdreColonnes(int $idColonne1, int $idColonne2): void;

    public function getAllFromColonne(int $idColonne): Colonne;
}