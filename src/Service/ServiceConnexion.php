<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Controleur\ControleurUtilisateur;
use App\Trellotrolle\Lib\ConnexionUtilisateurInterface;
use App\Trellotrolle\Lib\ConnexionUtilisateurSession;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Lib\MotDePasse;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\HTTP\Cookie;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepositoryInterface;
use App\Trellotrolle\Service\Exception\ConnexionException;
use App\Trellotrolle\Service\Exception\ServiceException;
use Symfony\Component\HttpFoundation\Response;

class ServiceConnexion implements ServiceConnexionInterface
{


    /**
     * ServiceConnexion constructor.
     * @param UtilisateurRepositoryInterface $utilisateurRepository Repository des utilisateurs
     * @param ConnexionUtilisateurInterface $connexionUtilisateurJWT Connexion utilisateur côté JWT
     * @param ConnexionUtilisateurInterface $connexionUtilisateurSession Connexion utilisateur côté session
     */
    public function __construct(private UtilisateurRepositoryInterface $utilisateurRepository,
                                private ConnexionUtilisateurInterface  $connexionUtilisateurJWT,
                                private ConnexionUtilisateurInterface  $connexionUtilisateurSession)
    {
    }


    /**
     * Fonction permettant de vérifier si l'utilisateur n'est pas connecté
     * @return void
     * @throws ConnexionException Si l'utilisateur n'est pas connecté
     */
    public function pasConnecter()
    {
        if (!$this->connexionUtilisateurJWT->estConnecte() && !$this->connexionUtilisateurSession->estConnecte()) {
            throw new ConnexionException("Veuillez vous connecter", Response::HTTP_FORBIDDEN);
        }
    }


    /**
     * Fonction permettant de vérifier si l'utilisateur est déjà connecté
     * @return void
     * @throws ConnexionException Si l'utilisateur est déjà connecté
     */
    public function dejaConnecter()
    {
        if ($this->connexionUtilisateurJWT->estConnecte() && $this->connexionUtilisateurSession->estConnecte()) {
            throw new ConnexionException("Vous êtes déjà connecté",Response::HTTP_FORBIDDEN);
        }
    }


    /**
     * Fonction permettant de déconnecter un utilisateur
     * @return void
     * @throws ConnexionException Si l'utilisateur n'est pas déjà connecté
     */
    public function deconnecter()
    {
        if (!$this->connexionUtilisateurSession->estConnecte() && !$this->connexionUtilisateurJWT->estConnecte()) {
            throw new ConnexionException("Utilisateur non connecté",Response::HTTP_FORBIDDEN);
        }
        $this->connexionUtilisateurSession->deconnecter();
        $this->connexionUtilisateurJWT->deconnecter();
    }


    /**
     * Fonction permettant de connecter un utilisateur
     * @param $login, Le login de l'utilisateur
     * @param $mdp, Le mot de passe de l'utilisateur
     * @return void
     * @throws ServiceException Si le login ou le mot de passe est manquant
     * ou si le login est inconnu ou si le mot de passe est incorrect
     */
    public function connecter($login, $mdp)
    {
        if (is_null($login) || is_null($mdp)) {
            //TODO ce messageFlash était en "danger", c'est maintenant un "warning"
            throw new ServiceException("Login ou mot de passe manquant",404);
        }

        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($login);

        if ($utilisateur == null) {
            throw new ServiceException("Login inconnu.",404);
        }

        if (!MotDePasse::verifier($mdp, $utilisateur->getMdpHache())) {
            throw new ServiceException("Mot de passe incorrect.",Response::HTTP_UNAUTHORIZED);
        }

        $this->connexionUtilisateurJWT->connecter($utilisateur->getLogin());
        $this->connexionUtilisateurSession->connecter($utilisateur->getLogin());
    }
}