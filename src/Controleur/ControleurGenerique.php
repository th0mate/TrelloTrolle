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

    protected function afficherVue(string $cheminVue, array $parametres = []): Response
    {
        extract($parametres);
//        $messagesFlash = $_REQUEST["messagesFlash"] ?? [];
        $messagesFlash = MessageFlash::lireTousMessages();
        ob_start();
        require __DIR__ . "/../vue/$cheminVue";
        $corpsReponse = ob_get_clean();
        return new Response($corpsReponse);
    }

    // https://stackoverflow.com/questions/768431/how-do-i-make-a-redirect-in-php
    protected function redirection(string $controleur = "", string $action = "", array $query = []) : RedirectResponse
    {
//        $queryString = [];
//        if ($action != "") {
//            $queryString[] = "action=$action";
//        }
//        if ($controleur != "") {
//            $queryString[] = "controleur=$controleur";
//        }
//        foreach ($query as $name => $value) {
//            $name = rawurlencode($name);
//            $value = rawurlencode($value);
//            $queryString[] = "$name=$value";
//        }
//        $url = "Location: ./controleurFrontal.php?" . join("&", $queryString);
//        header($url);
        $generateurUrl= $this->container->get("url_generator");
        $url = $generateurUrl->generate($action, $query);
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

        /*return ControleurGenerique::afficherVue('vueGenerale.php', [
            "pagetitle" => "Problème",
            "cheminVueBody" => "erreur.php",
            "messageErreur" => $messageErreurVue
        ]);*/
        return $this->afficherTwig('error.html.twig',["errorMessage" => $messageErreurVue]);
    }

    public function issetAndNotNull(array $requestParams) : bool {
        foreach ($requestParams as $param) {
            if(!(isset($_REQUEST[$param]) && $_REQUEST[$param] != null)) {
                return false;
            }
        }
        return true;
    }

    protected function redirectionConnectionFlash(ConnexionException $e): Response
    {
        MessageFlash::ajouter("info", $e->getMessage());
        return self::redirection("utilisateur", "afficherFormulaireConnexion");
    }
    protected function afficherTwig(string $cheminVue, array $parametres = []): Response
    {
        /** @var Environment $twig */
        $twig = $this->container->get("twig");
        $corpsReponse = $twig->render($cheminVue, $parametres);
        return new Response($corpsReponse);
    }

}