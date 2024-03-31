<?php

namespace App\Trellotrolle\Lib;

use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\HTTP\Session;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;

class ConnexionUtilisateurSession implements ConnexionUtilisateurInterface
{
    private string $cleConnexion = "_utilisateurConnecte";

    public function connecter(string $loginUtilisateur): void
    {
        $session = Session::getInstance();
        $session->enregistrer($this->cleConnexion, $loginUtilisateur);
    }

    public function estConnecte(): bool
    {
        $session = Session::getInstance();
        return $session->contient($this->cleConnexion);
    }

    public function deconnecter() : void
    {
        $session = Session::getInstance();
        $session->supprimer($this->cleConnexion);
    }

    public function getLoginUtilisateurConnecte(): ?string
    {
        $session = Session::getInstance();
        if ($session->contient($this->cleConnexion)) {
            return $session->lire($this->cleConnexion);
        } else
            return null;
    }

    public function estUtilisateur($login): bool
    {
        return ($this->estConnecte() &&
            $this->getLoginUtilisateurConnecte() == $login
        );
    }
}
