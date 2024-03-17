<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Controleur\ControleurUtilisateur;
use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Lib\MotDePasse;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\HTTP\Cookie;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Service\Exception\ConnexionException;
use App\Trellotrolle\Service\Exception\ServiceException;

class ServiceConnexion
{

    private UtilisateurRepository $utilisateurRepository;

    public function __construct()
    {
        $this->utilisateurRepository=new UtilisateurRepository();
    }

    /**
     * @throws ConnexionException     */
    public function pasConnecter()
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

    /**
     * @throws ServiceException
     */
    public function connecter($login, $mdp)
    {
        if (is_null($login) || is_null($mdp)){
            //TODO ce messageFlash était en "danger", c'est maintenant un "warning"
            throw new ServiceException("Login ou mot de passe manquant");
        }

        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($login);

        if ($utilisateur == null) {
            throw new ("Login inconnu.");
        }

        if (!MotDePasse::verifier($mdp, $utilisateur->getMdpHache())) {
            throw new ("Mot de passe incorrect.");
        }

        ConnexionUtilisateur::connecter($utilisateur->getLogin());
        Cookie::enregistrer("login",$login);
        Cookie::enregistrer("mdp", $mdp);
    }
}