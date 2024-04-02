<?php

namespace App\Trellotrolle\Lib;

use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\HTTP\Session;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;

class ConnexionUtilisateurSession implements ConnexionUtilisateurInterface
{
    /**
     * @var string La clé de connexion
     */
    private string $cleConnexion = "_utilisateurConnecte";

    /**
     * Fonction permettant de connecter un utilisateur
     * @param string $loginUtilisateur Le login de l'utilisateur à connecter
     * @return void
     */
    public function connecter(string $loginUtilisateur): void
    {
        $session = Session::getInstance();
        $session->enregistrer($this->cleConnexion, $loginUtilisateur);
    }

    /**
     * Fonction permettant de savoir si un utilisateur est connecté
     * @return bool Vrai si l'utilisateur est connecté, faux sinon
     */
    public function estConnecte(): bool
    {
        $session = Session::getInstance();
        return $session->contient($this->cleConnexion);
    }

    /**
     * Fonction permettant de déconnecter un utilisateur
     * @return void
     */
    public function deconnecter() : void
    {
        $session = Session::getInstance();
        $session->supprimer($this->cleConnexion);
    }

    /**
     * Fonction permettant de récupérer le login de l'utilisateur connecté
     * @return string|null Le login de l'utilisateur connecté
     */
    public function getLoginUtilisateurConnecte(): ?string
    {
        $session = Session::getInstance();
        if ($session->contient($this->cleConnexion)) {
            return $session->lire($this->cleConnexion);
        } else
            return null;
    }

    /**
     * Fonction permettant de savoir si l'utilisateur connecté est celui
     * passé en paramètre
     * @param $login , le login de l'utilisateur
     * @return bool Vrai si l'utilisateur connecté est celui passé en paramètre, faux sinon
     */
    public function estUtilisateur($login): bool
    {
        return ($this->estConnecte() &&
            $this->getLoginUtilisateurConnecte() == $login
        );
    }
}
