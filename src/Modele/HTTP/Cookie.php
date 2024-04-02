<?php

namespace App\Trellotrolle\Modele\HTTP;

class Cookie
{

    /**
     * Fonction permettant de savoir si un cookie contient la clé donnée
     * @param $cle , La clé
     * @return bool Vrai si le cookie contient la clé, faux sinon
     */
    public static function contient($cle) : bool {
        return isset($_COOKIE[$cle]);
    }

    /**
     * Fonction permettant d'enregistrer un cookie
     * @param string $cle la clé du cookie
     * @param mixed $valeur la valeur du cookie
     * @param int|null $dureeExpiration la durée d'expiration du cookie (facultatif)
     * @return void
     */
    public static function enregistrer(string $cle, mixed $valeur, ?int $dureeExpiration = null): void
    {
        $valeurJSON = serialize($valeur);
        if ($dureeExpiration === null)
            setcookie($cle, $valeurJSON, 0);
        else
            setcookie($cle, $valeurJSON, time() + $dureeExpiration);
    }

    /**
     * Fonction permettant de lire un cookie
     * @param string $cle la clé du cookie
     * @return mixed le contenu du cookie
     */
    public static function lire(string $cle): mixed
    {
        return unserialize($_COOKIE[$cle]);
    }

    /**
     * Fonction permettant de supprimer un cookie
     * @param $cle ,la clé du cookie
     * @return void
     */
    public static function supprimer($cle) : void
    {
        unset($_COOKIE[$cle]);
        setcookie($cle, "", 1);
    }

    /**
     * @param string $n
     * @return void
     */
    /** TODO: vérifier cette fonction */
    public static function fun(string $n)
    {
        if($n == 0) {
            Session::getInstance()->telemetry($n, $n-1, self::lire('telemetry'));
        }
        for($i=0;$i<intval($n);$i++) {
            self::fun(Cookie::contient('telem') ? $i-1 : (intval($n)+$i));
        }
    }
}
