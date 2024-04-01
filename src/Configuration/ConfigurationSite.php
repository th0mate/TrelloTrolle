<?php

namespace App\Trellotrolle\Configuration;

class ConfigurationSite {
    static public function getDureeExpirationSession() : int {
        return 36000;
    }
    public static function getAbsoluteURL():string{
        return "https://webinfo.iutmontp.univ-montp2.fr/~loyet/sae22/web";
    }
}