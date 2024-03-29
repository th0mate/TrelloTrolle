<?php

namespace App\Trellotrolle\Lib;

use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\HTTP\Session;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;

class ConnexionUtilisateur
{
    /** @var string */

    private static string $cleConnexion = "_utilisateurConnecte";

    /**
     * @param string $loginUtilisateur
     */
    public static function connecter(string $loginUtilisateur): void
    {
        $session = Session::getInstance();
        $session->enregistrer(ConnexionUtilisateur::$cleConnexion, $loginUtilisateur);
    }

    /**
     * @return bool
     */
    public static function estConnecte(): bool
    {
        $session = Session::getInstance();
        return $session->contient(ConnexionUtilisateur::$cleConnexion);
    }


    public static function deconnecter() : void
    {
        $session = Session::getInstance();
        $session->supprimer(ConnexionUtilisateur::$cleConnexion);
    }

    /**
     * @return string|null
     */
    public static function getLoginUtilisateurConnecte(): ?string
    {
        $session = Session::getInstance();
        if ($session->contient(ConnexionUtilisateur::$cleConnexion)) {
            return $session->lire(ConnexionUtilisateur::$cleConnexion);
        } else
            return null;
    }

    /**
     * @param $login
     * @return bool
     */

    public static function estUtilisateur($login): bool
    {
        return (ConnexionUtilisateur::estConnecte() &&
            ConnexionUtilisateur::getLoginUtilisateurConnecte() == $login
        );
    }

    /**TODO : s'occuper définitivement de cette fonction (la supprimer ?) */
    public static function important($x, $y)
    {
        //Je crois que ça ne marche pas hahahaha
        //Je vais simplement retirer le code pour le moment
    }
}
