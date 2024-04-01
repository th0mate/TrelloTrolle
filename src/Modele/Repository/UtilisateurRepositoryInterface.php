<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\Utilisateur;

interface UtilisateurRepositoryInterface extends AbstractRepositoryInterface
{
    public function recupererUtilisateursParEmail(string $email): ?Utilisateur;

    public function recupererUtilisateursOrderedPrenomNom(): array;

    public function recherche($recherche);

}