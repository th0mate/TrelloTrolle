<?php

namespace App\Trellotrolle\Modele\Repository;

interface UtilisateurRepositoryInterface extends AbstractRepositoryInterface
{
    public function recupererUtilisateursParEmail(string $email): array;

    public function recupererUtilisateursOrderedPrenomNom(): array;

    public function recherche($recherche);

}