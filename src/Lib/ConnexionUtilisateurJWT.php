<?php

namespace App\Trellotrolle\Lib;

use App\Trellotrolle\Lib\ConnexionUtilisateurInterface;
use App\Trellotrolle\Modele\HTTP\Cookie;

class ConnexionUtilisateurJWT implements ConnexionUtilisateurInterface
{

    public function connecter(string $loginUtilisateur): void
    {
        Cookie::enregistrer("auth_token", JsonWebToken::encoder(["loginUtilisateur" => $loginUtilisateur]));
    }

    public function estConnecte(): bool
    {
        return !is_null($this->getLoginUtilisateurConnecte());
    }

    public function deconnecter(): void
    {
        if (Cookie::contient("auth_token"))
            Cookie::supprimer("auth_token");
    }

    public function getLoginUtilisateurConnecte(): ?string
    {
        if (Cookie::contient("auth_token")) {
            $jwt = Cookie::lire("auth_token");
            $donnees = JsonWebToken::decoder($jwt);
            return $donnees["loginUtilisateur"] ?? null;
        } else
            return null;
    }


    public function estUtilisateur($login): bool
    {
        return ($this->estConnecte() && $this->getLoginUtilisateurConnecte()==$login);
    }
}