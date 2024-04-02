<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
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
     * Fonction permettant de récupérer une carte par son id
     * @param int|null $idCarte L'id de la carte à récupérer
     * @return Carte La carte récupérée
     * @throws ServiceException
     */

    public function recupererCarte(?int $idCarte): Carte;

    /**
     * Fonction permettant de supprimer une carte d'un tableau
     * @param int $idCarte  L'id de la carte à supprimer
     * @return array  Les cartes du tableau après suppression de la carte donnée
     */
    public function supprimerCarte(Tableau $tableau, int $idCarte): array;

    /**
     * Fonction permettant de créer une carte
     * @param Tableau $tableau Le tableau sur lequel on veut créer la carte
     * @param array $attributs Les attributs de la carte
     * @param Colonne $colonne La colonne dans laquelle on veut créer la carte
     * @return Carte La carte créée
     * @throws CreationException
     */
    public function creerCarte(Tableau $tableau, array $attributs, Colonne $colonne): Carte;


    /**
     * Fonction permettant de récupérer les attributs d'une carte
     * @param array $attributs Les attributs de la carte à récupérer
     * @return void
     * @throws CreationException
     */
    public function recupererAttributs(array $attributs): void;


    /**
     * Fonction permettant de mettre à jour une carte
     * @param Tableau $tableau Le tableau sur lequel on veut mettre à jour la carte
     * @param $attributs, Les attributs de la carte
     * @param Carte $carte La carte à mettre à jour
     * @param Colonne $colonne La colonne dans laquelle on veut mettre à jour la carte
     * @return Carte La carte mise à jour
     */
    public function miseAJourCarte(Tableau $tableau, $attributs, Carte $carte, Colonne $colonne): Carte;



    /**
     * Fonction permettant de vérifier si une carte peut être mise à jour
     * @param $idCarte L'id de la carte à mettre à jour
     * @param Colonne $colonne La colonne dans laquelle on veut mettre à jour la carte
     * @param $attributs, Les attributs de la carte
     * @return Carte La carte pouvant être mise à jour
     */
    public function verificationsMiseAJourCarte(int $idCarte, Colonne $colonne, $attributs): Carte;

    /**
     * Fonction permettant de mettre à jour les membres d'une carte
     * @param Tableau $tableau  Le tableau sur lequel on veut mettre à jour les membres de la carte
     * @param AbstractDataObject $utilisateur  L'utilisateur à mettre à jour
     * @return mixed
     */
    public function miseAJourCarteMembre(Tableau $tableau, AbstractDataObject $utilisateur): void;

    /**
     * Fonction permettant de récupérer les membres d'une carte
     * @return mixed
     */
    public function getNextIdCarte(): int;


    /**
     * Fonction permettant de déplacer une carte d'une colonne à une autre
     * @param Carte $carte La carte à déplacer
     * @param Colonne $colonne La colonne dans laquelle on veut déplacer la carte
     * @return void
     */
    public function deplacerCarte(Carte $carte,Colonne $colonne): void;



}