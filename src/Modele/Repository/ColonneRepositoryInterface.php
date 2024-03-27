<?php

namespace App\Trellotrolle\Modele\Repository;

interface ColonneRepositoryInterface
{
    public function recupererColonnesTableau(int $idTableau): array;

    public function getNextIdColonne(): int;

    public function getNombreColonnesTotalTableau(int $idTableau): int;
}