<?php

namespace App\Trellotrolle\Configuration;

class ConfigurationSite {
    static public function getDureeExpirationSession() : int {
        return 36000;
    }
}