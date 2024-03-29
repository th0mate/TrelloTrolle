<?php

namespace App\Trellotrolle\Modele\HTTP;

class Cookie
{

    /**
     * @param $cle
     * @return bool
     */
    public static function contient($cle) : bool {
        return isset($_COOKIE[$cle]);
    }

    /**
     * @param string $cle
     * @param mixed $valeur
     * @param int|null $dureeExpiration
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
     * @param string $cle
     * @return mixed
     */
    public static function lire(string $cle): mixed
    {
        return unserialize($_COOKIE[$cle]);
    }

    /**
     * @param $cle
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
