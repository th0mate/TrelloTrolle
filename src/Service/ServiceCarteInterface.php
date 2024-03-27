<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Service\Exception\CreationException;
use App\Trellotrolle\Service\Exception\MiseAJourException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;

interface ServiceCarteInterface
{
    /**
     * @throws ServiceException
     */
    public function recupererCarte($idCarte): Carte;

    /**
     * @throws TableauException
     */
    public function supprimerCarte(Tableau $tableau, $idCarte): array;

    /**
     * @throws CreationException
     */
    public function creerCarte(Tableau $tableau, $attributs, Colonne $colonne): Carte;

    public function newCarte(Colonne $colonne, $attributs): Carte;

    /**
     * @throws CreationException
     */
    public function recupererAttributs($attributs): void;

    /**
     * @throws CreationException
     * @throws MiseAJourException
     */
    public function miseAJourCarte(Tableau $tableau, $attributs, Carte $carte, Colonne $colonne): Carte;

    public function carteUpdate(Carte $carte, Colonne $colonne, $attributs): Carte;

    /**
     * @throws CreationException
     * @throws ServiceException
     */
    public function verificationsMiseAJourCarte($idCarte, Colonne $colonne, $attributs): Carte;

    public function miseAJourCarteMembre(Tableau $tableau, Utilisateur $utilisateur): void;
    public function getNextIdCarte(): int;
    public function deplacerCarte(Carte $carte,Colonne $colonne): void;

}