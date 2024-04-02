<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\Conteneur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Service\Exception\ConnexionException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class ControleurGenerique {


    public function __construct(private ContainerInterface $container)
    {
    }

    // https://stackoverflow.com/questions/768431/how-do-i-make-a-redirect-in-php
    protected function redirection(string $route = "accueil", array $parameters = []) : RedirectResponse
    {
        $generateurUrl= $this->container->get("url_generator");
        $url = $generateurUrl->generate($route, $parameters);
        var_dump($url);
        return new RedirectResponse($url);
    }

    public function afficherErreur($messageErreur = "", $controleur = ""): Response
    {
        $messageErreurVue = "Problème";
        if ($controleur !== "")
            $messageErreurVue .= " avec le contrôleur $controleur";
        if ($messageErreur !== "")
            $messageErreurVue .= " : $messageErreur";
        return $this->afficherTwig('error.html.twig',["errorMessage" => $messageErreurVue]);
    }
    
    protected function redirectionConnectionFlash(ConnexionException $e): Response
    {
        MessageFlash::ajouter("info", $e->getMessage());
        return self::redirection("afficherFormulaireConnexion");
    }
    protected function afficherTwig(string $cheminVue, array $parametres = []): Response
    {
        /** @var Environment $twig */
        $twig = $this->container->get("twig");
        $corpsReponse = $twig->render($cheminVue, $parametres);
        return new Response($corpsReponse);
    }

}