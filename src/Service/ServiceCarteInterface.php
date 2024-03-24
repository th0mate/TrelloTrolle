<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Service\Exception\CreationException;
use App\Trellotrolle\Service\Exception\MiseAJourException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;

/**
 * Service permettant de gérer les différentes actions que l'utilisateur peut réaliser sur une carte
 */
interface ServiceCarteInterface
{
    /**
     * récupére une carte grâce à l'id passé en paramètre
     * @param int|null $idCarte l'id de la carte à récuperer
     * @return Carte La carte récupéré <code>non null</code> grâce à l'id
     * @throws ServiceException si l'id de la carte est<code>null</code> ou si elle ne correspond à aucune carte existante
     */

    public function recupererCarte(?int $idCarte): Carte;

    /**
     * Supprime la carte dont l'id est donné en paramètre
     * @param int $idCarte l'id de la carte à supprimer
     * @return void
     */
    public function supprimerCarte(int $idCarte): void;

    /**
     * Créer une carte
     * @param Tableau $tableau le tableau dans lequel la carte est créer
     * @param array $attributs les attributs de la carte à creer (titreCarte,descriptifCarte,couleurCarte,affectationsCarte)
     * @param Colonne $colonne la colonne dans laquel la carte est créer
     * @return Carte la carte créé
     * @throws CreationException si un membre à affecter n'existe pas ou s'il n'est pas collaborateur du tableau
     */
    public function creerCarte(Tableau $tableau, array $attributs, Colonne $colonne): Carte;


    /**
     * Vérifie qu'il n'y ai pas un attribut qui soit <code>null</code>
     * @param array $attributs un tableau contenant les attributs à vérifier
     * @return void
     * @throws CreationException si l'un des attributs est <code>null</code>
     */
    public function recupererAttributs(array $attributs): void;

    /**
     * @throws CreationException
     * @throws MiseAJourException
     */
    public function miseAJourCarte($tableau, $attributs, $carte, $colonne);

    /**
     * @param Carte $carte
     * @param $colonne
     * @param $attributs
     * @return Carte
     */
    public function carteUpdate(Carte $carte, $colonne, $attributs): Carte;

    /**
     * @throws CreationException
     * @throws ServiceException
     */
    public function verificationsMiseAJourCarte($idCarte, $colonne, $attributs);

    /**
     * @param $tableau
     * @param $utilisateur
     * @return mixed
     */
    public function miseAJourCarteMembre($tableau, $utilisateur);

    /**
     * @return mixed
     */
    public function getNextIdCarte();
}