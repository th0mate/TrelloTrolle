<?php

namespace App\Trellotrolle\Lib;

use App\Trellotrolle\Modele\HTTP\Session;

class MessageFlash
{

    /**
     * @var string La clé de stockage des messages flash
     */
    private static string $cleFlash = "_messagesFlash";

    /**
     * Fonction qui ajoute un message flash
     * @param string $type Le type du message (success, info, warning, danger)
     * @param string $message Le message à afficher
     */
    public static function ajouter(string $type, string $message): void
    {
        $session = Session::getInstance();

        $messagesFlash = [];
        if ($session->contient(MessageFlash::$cleFlash))
            $messagesFlash = $session->lire(MessageFlash::$cleFlash);

        $messagesFlash[$type][] = $message;
        $session->enregistrer(MessageFlash::$cleFlash, $messagesFlash);
    }

    /**
     * Fonction qui vérifie si un type précis de message est présent
     * @param string $type Le type du message (success, info, warning, danger)
     * @return bool Vrai si le message du bon type est présent, faux sinon
     */
    public static function contientMessage(string $type): bool
    {
        $session = Session::getInstance();
        return $session->contient(MessageFlash::$cleFlash) &&
            array_key_exists($type, $session->lire(MessageFlash::$cleFlash))  &&
            !empty($session->lire(MessageFlash::$cleFlash)[$type]);
    }

    /**
     * Fonction qui lit les messages flash d'un type donné
     * @param string $type Le type du message (success, info, warning, danger)
     * @return array Les messages flash du type donné
     */
    public static function lireMessages(string $type): array
    {
        $session = Session::getInstance();
        if (!MessageFlash::contientMessage($type))
            return [];

        $messagesFlash = $session->lire(MessageFlash::$cleFlash);
        $messages = $messagesFlash[$type];
        unset($messagesFlash[$type]);
        $session->enregistrer(MessageFlash::$cleFlash, $messagesFlash);

        return $messages;
    }

    /**
     * Fonction qui lit tous les messages flash
     * @return array Tous les messages flash de tous les types
     */
    public static function lireTousMessages() : array
    {
        $tousMessages = [];
        foreach(["success", "info", "warning", "danger"] as $type) {
            $tousMessages[$type] = MessageFlash::lireMessages($type);
        }
        return $tousMessages;
    }

}