<?php

namespace App\Trellotrolle\Modele\Repository;

interface UtilisateurRepositoryInterface
{
    /**
     * @param string $email
     * @return array
     */
    public function recupererUtilisateursParEmail(string $email): array;

    /**
     * @return array
     */
    public function recupererUtilisateursOrderedPrenomNom(): array;

    /**
     * @param $recherche
     * @return mixed
     */
    public function recherche($recherche);
}