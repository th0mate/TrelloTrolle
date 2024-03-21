<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Service\Exception\ConnexionException;
use App\Trellotrolle\Service\Exception\ServiceException;

interface ServiceConnexionInterface
{
    /**
     * @throws ConnexionException
     */
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
     * @throws ServiceException
     */
    public function connecter($login, $mdp);
}