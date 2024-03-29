<?php

namespace App\Trellotrolle\Modele\Repository;

interface ColonneRepositoryInterface
{
    /**
     * @param int $idTableau
     * @return array
     */
    public function recupererColonnesTableau(int $idTableau): array;

    /**
     * @return int
     */
    public function getNextIdColonne(): int;

    /**
     * @param int $idTableau
     * @return int
     */
    public function getNombreColonnesTotalTableau(int $idTableau): int;

    /**
     * @param int $idColonne1
     * @param int $idColonne2
     * @return void
     */
    public function inverserOrdreColonnes(int $idColonne1, int $idColonne2): void;

    /**
     * @param int $idColonne
     * @return array
     */
    public function getAllFromColonne(int $idColonne): array;
}