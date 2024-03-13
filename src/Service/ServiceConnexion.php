<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Service\Exception\ConnexionException;

class ServiceConnexion
{

    /**
     * @throws ConnexionException     */
    public function connecter()
    {
        if(!ConnexionUtilisateur::estConnecte()) {
            throw new ConnexionException("Veuillez vous connecter");
        }
    }
}