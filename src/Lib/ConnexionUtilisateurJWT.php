<?php

namespace App\Trellotrolle\Lib;

use App\Trellotrolle\Lib\ConnexionUtilisateurInterface;
use App\Trellotrolle\Modele\HTTP\Cookie;

class ConnexionUtilisateurJWT implements ConnexionUtilisateurInterface
{

    /**
     * Fonction permettant de connecter un utilisateur
     * @param string $loginUtilisateur Le login de l'utilisateur à connecter
     * @return void
     */
    public function connecter(string $loginUtilisateur): void
    {
        Cookie::enregistrer("auth_token", JsonWebToken::encoder(["loginUtilisateur" => $loginUtilisateur]));
    }

    /**
     * Fonction permettant de savoir si un utilisateur est connecté
     * @return bool Vrai si l'utilisateur est connecté, faux sinon
     */
    public function estConnecte(): bool
    {
        return !is_null($this->getLoginUtilisateurConnecte());
    }

    /**
     * Fonction permettant de déconnecter un utilisateur
     * @return void
     */
    public function deconnecter(): void
    {
        if (Cookie::contient("auth_token"))
            Cookie::supprimer("auth_token");
    }

    /**
     * Fonction permettant de récupérer le login de l'utilisateur connecté
     * @return string|null Le login de l'utilisateur connecté
     */
    public function getLoginUtilisateurConnecte(): ?string
    {
        if (Cookie::contient("auth_token")) {
            $jwt = Cookie::lire("auth_token");
            $donnees = JsonWebToken::decoder($jwt);
            return $donnees["loginUtilisateur"] ?? null;
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
        return ($this->estConnecte() && $this->getLoginUtilisateurConnecte()==$login);
    }
}