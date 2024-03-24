<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\Carte;
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
     * @param $idCarte l'id de la carte à récuperer
     * @return Carte La carte récupéré <code>non null</code> grâce à l'id
     * @throws ServiceException si l'id de la carte est<code>null</code> ou si elle ne correspond à aucune carte existante
     */
    public function recupererCarte($idCarte): Carte;

    /**
     * @param $idCarte
     * @return void
     */
    public function supprimerCarte($idCarte): void;

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
}