<?php

namespace App\Trellotrolle\Controleur;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;

class ControleurBase extends ControleurGenerique
{
    #[Route('/', name: 'accueil')]
    public static function accueil():  Response {
       return ControleurBase::afficherVue('vueGenerale.php', [
            "pagetitle" => "Accueil",
            "cheminVueBody" => "base/accueil.php"
        ]);
    }
}