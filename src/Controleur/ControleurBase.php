<?php

namespace App\Trellotrolle\Controleur;

class ControleurBase extends ControleurGenerique
{
    public static function accueil() {
        ControleurBase::afficherVue('vueGenerale.php', [
            "pagetitle" => "Accueil",
            "cheminVueBody" => "base/accueil.php"
        ]);
    }
}