<?php

namespace App\Trellotrolle\Controleur;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Lib\Conteneur;
use Symfony\Component\HttpFoundation\RedirectResponse;


class ControleurGenerique {



    protected static function afficherVue(string $cheminVue, array $parametres = []): Response
    {
        extract($parametres);
        $messagesFlash = MessageFlash::lireTousMessages();
        ob_start();
        require __DIR__ . "/../vue/$cheminVue";
        $corpsReponse = ob_get_clean();
        return new Response($corpsReponse);
    }

    // https://stackoverflow.com/questions/768431/how-do-i-make-a-redirect-in-php
    public function redirection(string $nomRoute ,  array $params = []) : RedirectResponse
    {

        $url = Conteneur::recupererService($nomRoute). join("&", $params);
        header($url);
        return new RedirectResponse($url);
    }

    public static function afficherErreur($messageErreur = "", $controleur = ""): Response
    {
        $messageErreurVue = "Problème";
        if ($controleur !== "")
            $messageErreurVue .= " avec le contrôleur $controleur";
        if ($messageErreur !== "")
            $messageErreurVue .= " : $messageErreur";

        $reponse = ControleurGenerique::afficherVue('vueGenerale.php', [
            "pagetitle" => "Problème",
            "cheminVueBody" => "erreur.php",
            "messageErreur" => $messageErreurVue
        ]);
        $reponse->setStatusCode(400);
        return $reponse;
    }

    public static function issetAndNotNull(array $requestParams) : bool {
        foreach ($requestParams as $param) {
            if(!(isset($_REQUEST[$param]) && $_REQUEST[$param] != null)) {
                return false;
            }
        }
        return true;
    }
}