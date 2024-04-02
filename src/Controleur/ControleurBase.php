<?php

namespace App\Trellotrolle\Controleur;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;

class ControleurBase extends ControleurGenerique
{

    /**
     * ControleurBase constructor.
     * @param ContainerInterface $container le conteneur de dépendances
     *
     * fonction qui permet de construire le controleur de base
     */

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

    }

    /**
     * @return Response l'affichage de la page d'accueil du site en twig
     *
     * fonction qui permet d'afficher la page d'accueil
     */

    #[Route('/', name: 'accueil')]
    public function accueil():  Response {
        return $this->afficherTwig('base/accueil.html.twig');
    }
}