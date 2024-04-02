<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Service\Exception\ConnexionException;
use App\Trellotrolle\Service\Exception\ServiceException;

interface ServiceConnexionInterface
{

    /**
     * Fonction permettant de vérifier si l'utilisateur n'est pas connecté
     * @throws ConnexionException Si l'utilisateur n'est pas connecté
     * @return mixed
     */
    public function pasConnecter();

    /**
     * Fonction permettant de vérifier si l'utilisateur est déjà connecté
     * @throws ConnexionException Si l'utilisateur est déjà connecté
     */
    public function dejaConnecter();

    /**
     * Fonction permettant de déconnecter un utilisateur
     * @throws ServiceException Si l'utilisateur n'est pas déjà connecté
     */
    public function deconnecter();


    /**
     * Fonction permettant de connecter un utilisateur
     * @param $login, Le login de l'utilisateur
     * @param $mdp, Le mot de passe de l'utilisateur
     * @return mixed
     * @throws ServiceException
     */
    public function connecter($login, $mdp);
}