<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
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
    public function supprimerCarte($tableau, $idCarte): array;

    /**
     * @throws CreationException
     */
    public function creerCarte($tableau, $attributs, $colonne);

    public function newCarte($colonne, $attributs): Carte;

    /**
     * @throws CreationException
     */
    public function recupererAttributs($attributs): void;

    /**
     * @throws CreationException
     * @throws MiseAJourException
     */
    public function miseAJourCarte($tableau, $attributs, $carte, $colonne);

    public function carteUpdate(Carte $carte, $colonne, $attributs): Carte;

    /**
     * @throws CreationException
     * @throws ServiceException
     */
    public function verificationsMiseAJourCarte($idCarte, $colonne, $attributs);

    public function miseAJourCarteMembre($tableau, $utilisateur);
    public function getNextIdCarte();
    public function deplacerCarte(Carte $carte,Colonne $colonne);
    public function getAffectations(Carte $carte) :array;


}