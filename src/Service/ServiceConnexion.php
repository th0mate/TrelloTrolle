<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Controleur\ControleurUtilisateur;
use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Service\Exception\ConnexionException;
use App\Trellotrolle\Service\Exception\ServiceException;

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

    /**
     * @throws ConnexionException
     */
    public function dejaConnecter()
    {
        if (ConnexionUtilisateur::estConnecte()){
            throw new ConnexionException("Vous êtes déjà connecter");
        }
    }

    /**
     * @throws ServiceException
     */
    public function deconnecter()
    {
        if (!ConnexionUtilisateur::estConnecte()) {
            throw new ServiceException("Utilisateur non connecté");
        }
        ConnexionUtilisateur::deconnecter();
    }
}