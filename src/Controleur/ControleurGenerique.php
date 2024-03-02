<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\MessageFlash;

class ControleurGenerique {

    protected static function afficherVue(string $cheminVue, array $parametres = []): void
    {
        extract($parametres);
//        $messagesFlash = $_REQUEST["messagesFlash"] ?? [];
        $messagesFlash = MessageFlash::lireTousMessages();
        require __DIR__ . "/../vue/$cheminVue";
    }

    // https://stackoverflow.com/questions/768431/how-do-i-make-a-redirect-in-php
    protected static function redirection(string $controleur = "", string $action = "", array $query = []) : void
    {
        $queryString = [];
        if ($action != "") {
            $queryString[] = "action=$action";
        }
        if ($controleur != "") {
            $queryString[] = "controleur=$controleur";
        }
        foreach ($query as $name => $value) {
            $name = rawurlencode($name);
            $value = rawurlencode($value);
            $queryString[] = "$name=$value";
        }
        $url = "Location: ./controleurFrontal.php?" . join("&", $queryString);
        header($url);
        exit();
    }

    public static function afficherErreur($messageErreur = "", $controleur = ""): void
    {
        $messageErreurVue = "Problème";
        if ($controleur !== "")
            $messageErreurVue .= " avec le contrôleur $controleur";
        if ($messageErreur !== "")
            $messageErreurVue .= " : $messageErreur";

        ControleurGenerique::afficherVue('vueGenerale.php', [
            "pagetitle" => "Problème",
            "cheminVueBody" => "erreur.php",
            "messageErreur" => $messageErreurVue
        ]);
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