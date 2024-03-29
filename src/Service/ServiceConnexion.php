<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Controleur\ControleurUtilisateur;
use App\Trellotrolle\Lib\ConnexionUtilisateur;
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


    public function __construct(private UtilisateurRepositoryInterface $utilisateurRepository)
    {
    }

    /**
     * @throws ConnexionException
     */
    public function pasConnecter()
    {
        if (!ConnexionUtilisateur::estConnecte()) {
            throw new ConnexionException("Veuillez vous connecter", Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * @throws ConnexionException
     */
    public function dejaConnecter()
    {
        if (ConnexionUtilisateur::estConnecte()) {
            throw new ConnexionException("Vous êtes déjà connecté",Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * @throws ServiceException
     */
    public function deconnecter()
    {
        if (!ConnexionUtilisateur::estConnecte()) {
            throw new ConnexionException("Utilisateur non connecté",Response::HTTP_FORBIDDEN);
        }
        ConnexionUtilisateur::deconnecter();
    }

    /**
     * @throws ServiceException
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

        ConnexionUtilisateur::connecter($utilisateur->getLogin());
    }
}