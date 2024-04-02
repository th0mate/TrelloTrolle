<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Carte;

interface CarteRepositoryInterface
{
    /**
     * Fonction permettant de récupérer une carte en fonction de la clé primaire
     * @param int $idcolonne La clé primaire
     * @return array Les cartes récupérées
     */
    public function recupererCartesColonne(int $idcolonne): array;

    /**
     * Fonction permettant de récupérer toutes les cartes d'un tableau en fonction de l'id du tableau
     * @param int $idTableau L'id du tableau
     * @return array Les cartes du tableau
     */
    public function recupererCartesTableau(int $idTableau): array;

    /**
     * Fonction permettant de récupérer toutes les cartes d'un utilisateur en fonction de son login
     * @return Carte[] Les cartes de l'utilisateur
     */
    public function recupererCartesUtilisateur(string $login): array;

    /**
     * Fonction permettant de récupérer le nombre de cartes total d'un utilisateur
     * @param string $login Le login de l'utilisateur
     * @return int Le nombre de cartes total de l'utilisateur
     */
    public function getNombreCartesTotalUtilisateur(string $login): int;

    /**
     * Fonction permettant d'avoir l'id de la prochaine carte
     * @return int L'id de la prochaine carte
     */
    public function getNextIdCarte(): int;

    /**
     * Fonction permettant de récupérer les affectations d'une carte
     * @param Carte $carte La carte
     * @return array|null Les affectations de la carte
     */
    public function getAffectationsCarte(Carte $carte): ?array;

    /**
     * Fonction permettant de mettre à jour les affectations d'une carte
     * @param array|null $affectationsCarte Les nouvelles affectations de la carte
     * @param Carte $carte La carte
     * @return void
     */
    public function setAffectationsCarte(?array $affectationsCarte, Carte $carte): void;


}