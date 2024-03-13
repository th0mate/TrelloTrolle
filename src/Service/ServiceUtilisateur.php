<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Controleur\ControleurCarte;
use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Service\Exception\TableauException;

class ServiceUtilisateur
{

    /**
     * @throws TableauException
     */
    public function estParticipant($tableau)
    {
        if (!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            throw new TableauException("Vous n'avez pas de droits d'éditions sur ce tableau",$tableau);
        }
    }
}