<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Service\Exception\ConnexionException;
use App\Trellotrolle\Service\Exception\ServiceException;

interface ServiceConnexionInterface
{

    public function pasConnecter();

    /**
     * @throws ConnexionException
     */
    public function dejaConnecter();

    /**
     * @throws ServiceException
     */
    public function deconnecter();


    /**
     * @param $login
     * @param $mdp
     * @return mixed
     * @throws ServiceException
     */
    public function connecter($login, $mdp);
}