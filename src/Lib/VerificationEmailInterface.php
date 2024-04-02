<?php

namespace App\Trellotrolle\Lib;


use App\Trellotrolle\Modele\DataObject\Utilisateur;

/**
 * La classe VerificationEmail gère l'envoi d'e-mails de changement de mot de passe.
 */
interface VerificationEmailInterface
{
    /**
     * Envoie un e-mail de changement de mot de passe.
     *
     * @param string $login Le login de l'utilisateur pour lequel le mot de passe est changé.
     * @param string $mail L'adresse e-mail à laquelle envoyer l'e-mail.
     * @return void
     */
    public function envoiEmailChangementPassword(Utilisateur $utilisateur): void;
}