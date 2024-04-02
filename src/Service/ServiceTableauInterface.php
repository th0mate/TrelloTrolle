<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\AbstractRepository;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;

interface ServiceTableauInterface
{

    /**
     * Fonction permettant de récupérer un tableau par son id
     * @param $idTableau L'id du tableau à récupérer
     * @return Tableau Le tableau récupéré
     */
    public function recupererTableauParId($idTableau): Tableau;


    /**
     * Fonction permettant de récupérer un tableau par son code
     * @param $codeTableau,Le code du tableau à récupérer
     * @return Tableau Le tableau récupéré
     */
    public function recupererTableauParCode($codeTableau): Tableau;

    /**
     * Fonction permettant de récupérer les cartes des colonnes d'un tableau
     * @param Tableau $tableau Le tableau dont on veut récupérer les cartes
     * @return array Les cartes des colonnes du tableau
     */
    public function recupererCartesColonnes(Tableau $tableau): array;

    /**
     * Fonction permettant de récupérer un membre d'un tableau
     * @param $login, Le login du membre à récupérer
     * @return array Le membre récupéré
     */
    public function recupererTableauEstMembre($login): array;


    /**
     * Fonction permettant de vérifier si un tableau est null via son nom
     * @param $nomTableau, Le nom du tableau à vérifier
     * @param Tableau $tableau Le tableau à vérifier
     * @return void
     */
    public function isNotNullNomTableau($nomTableau, Tableau $tableau): void;

    /**
     * Fonction permettant de mettre à jour un tableau
     * @param Tableau $tableau Le tableau à mettre à jour
     * @return void
     */
    public function mettreAJourTableau(Tableau $tableau): void;


    /**
     * Fonction permettant de supprimer un tableau
     * @param $idTableau L'id du tableau à supprimer
     * @return void
     * @throws ServiceException
     */
    public function supprimerTableau($idTableau): void;


    /**
     * Fonction permettant de quitter un tableau
     * @param Tableau $tableau Le tableau à quitter
     * @param AbstractDataObject $utilisateur L'utilisateur qui quitte le tableau
     * @return void
     * @throws ServiceException
     */
    public function quitterTableau(Tableau $tableau, AbstractDataObject $utilisateur): void;


    /**
     * Fonction permettant de créer un tableau
     * @param $nomTableau, Le nom du tableau à créer
     * @param $login, Le login de l'utilisateur qui crée le tableau
     * @return mixed
     * @throws ServiceException
     */
    public function creerTableau($nomTableau, $login);


    /**
     * Fonction permettant de vérifier si un utilisateur est participant à un tableau
     * @param Tableau $tableau Le tableau sur lequel on veut vérifier si
     * l'utilisateur est participant
     * @param $login, Le login de l'utilisateur à vérifier
     * @return mixed
     */
    public function estParticipant(Tableau $tableau, $login);
}