<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Carte;

interface CarteRepositoryInterface
{
    /**
     * @param int $idcolonne
     * @return array
     */
    public function recupererCartesColonne(int $idcolonne): array;

    /**
     * @param int $idTableau
     * @return array
     */
    public function recupererCartesTableau(int $idTableau): array;

    /**
     * @return Carte[]
     */
    public function recupererCartesUtilisateur(string $login): array;

    /**
     * @param string $login
     * @return int
     */
    public function getNombreCartesTotalUtilisateur(string $login): int;

    /**
     * @return int
     */
    public function getNextIdCarte(): int;

    /**
     * @param Carte $carte
     * @return array|null
     */
    public function getAffectationsCarte(Carte $carte): ?array;

    /**
     * @param array|null $affectationsCarte
     * @param Carte $carte
     * @return void
     */
    public function setAffectationsCarte(?array $affectationsCarte, Carte $carte): void;

    /**
     * @param int $idCarte
     * @return Carte
     */
    public function getAllFromCartes(int $idCarte): Carte;
}