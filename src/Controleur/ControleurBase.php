<?php

namespace App\Trellotrolle\Controleur;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;

class ControleurBase extends ControleurGenerique
{

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

    }

    #[Route('/', name: 'accueil')]
    public function accueil():  Response {
       /*return ControleurBase::afficherVue('vueGenerale.php', [
            "pagetitle" => "Accueil",
            "cheminVueBody" => "base/accueil.php"
        ]);*/
        return $this->afficherTwig('base/accueil.php');
    }
}