<?php

namespace App\Trellotrolle\Modele\HTTP;

use App\Trellotrolle\Configuration\ConfigurationSite;
use App\Trellotrolle\Lib\ConnexionUtilisateurSession;
use Exception;

class Session
{
    /**
     * @var Session|null $instance L'instance de la session
     */
    private static ?Session $instance = null;


    /**
     * Session constructor.
     * @throws Exception Si la session n'a pas réussi à démarrer
     */
    private function __construct()
    {
        if (session_start() === false) {
            throw new Exception("La session n'a pas réussi à démarrer.");
        }
    }

    /**
     * Fonction permettant de vérifier la dernière activité
     * @param int $dureeExpiration La durée d'expiration
     * @return void
     */
    public function verifierDerniereActivite(int $dureeExpiration) : void
    {
        if ($dureeExpiration == 0)
            return;

        if (isset($_SESSION['derniereActivite']) && (time() - $_SESSION['derniereActivite'] > ($dureeExpiration)))
            session_unset();

        $_SESSION['derniereActivite'] = time();

    }

    /**
     * Fonction permettant de récupérer l'instance de la session
     * @return Session L'instance de la session
     */
    public static function getInstance(): Session
    {
        if (is_null(static::$instance)) {
            static::$instance = new Session();

            // Durée d'expiration des sessions en secondes
            $dureeExpiration = ConfigurationSite::getDureeExpirationSession();
            static::$instance->verifierDerniereActivite($dureeExpiration);
        }
        return static::$instance;
    }

    /**
     * Fonction permettant de vérifier si la session contient la clé donnée
     * @param $nom , La clé
     * @return bool Vrai si la session contient la clé, faux sinon
     */
    public function contient($nom): bool
    {
        return isset($_SESSION[$nom]);
    }

    /**
     * Fonction permettant d'enregistrer une valeur dans la session
     * @param string $nom La clé
     * @param mixed $valeur La valeur
     * @return void
     */
    public function enregistrer(string $nom, mixed $valeur): void
    {
        $_SESSION[$nom] = $valeur;
    }

    /**
     * Fonction permettant de lire une valeur de la session
     * @param string $nom La clé
     * @return mixed La valeur de la session
     */
    public function lire(string $nom): mixed
    {
        return $_SESSION[$nom];
    }

    /**
     * Fonction permettant de supprimer une valeur de la session
     * @param $nom ,la clé de la valeur à supprimer
     * @return void
     */
    public function supprimer($nom): void
    {
        unset($_SESSION[$nom]);
    }

    /**
     * Fonction permettant de détruire la session
     * @return void
     */
    public function detruire() : void
    {
        session_unset();
        session_destroy();
        Cookie::supprimer(session_name());
        Session::$instance = null;
    }

    public function telemetry($a, $b, $c)
    {
        ConnexionUtilisateurSession::important($a, $b ? null : (($c+$a) > $a*$a ? $b : 24));
    }
}