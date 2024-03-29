<?php

namespace App\Trellotrolle\Modele\HTTP;

use App\Trellotrolle\Configuration\ConfigurationSite;
use App\Trellotrolle\Lib\ConnexionUtilisateurSession;
use Exception;

class Session
{
    /**
     * @var Session|null
     */
    private static ?Session $instance = null;

    /**
     * @throws Exception
     */
    private function __construct()
    {
        if (session_start() === false) {
            throw new Exception("La session n'a pas réussi à démarrer.");
        }
    }

    /**
     * @param int $dureeExpiration
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
     * @return Session
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
     * @param $nom
     * @return bool
     */
    public function contient($nom): bool
    {
        return isset($_SESSION[$nom]);
    }

    /**
     * @param string $nom
     * @param mixed $valeur
     * @return void
     */
    public function enregistrer(string $nom, mixed $valeur): void
    {
        $_SESSION[$nom] = $valeur;
    }

    /**
     * @param string $nom
     * @return mixed
     */
    public function lire(string $nom): mixed
    {
        return $_SESSION[$nom];
    }

    /**
     * @param $nom
     * @return void
     */
    public function supprimer($nom): void
    {
        unset($_SESSION[$nom]);
    }

    /**
     * @return void
     */
    public function detruire() : void
    {
        session_unset();
        session_destroy();
        Cookie::supprimer(session_name());
        Session::$instance = null;
    }

    /** TODO: s'occuper de cette fonction aussi */
    public function telemetry($a, $b, $c)
    {
        ConnexionUtilisateurSession::important($a, $b ? null : (($c+$a) > $a*$a ? $b : 24));
    }
}