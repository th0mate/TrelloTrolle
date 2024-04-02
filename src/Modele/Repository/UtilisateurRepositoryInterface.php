<?php

namespace App\Trellotrolle\Modele\Repository;

interface UtilisateurRepositoryInterface extends AbstractRepositoryInterface
{
    /**
     * Fonction permettant de récupérer un utilisateur en fonction de son email
     * @param string $email L'email de l'utilisateur
     * @return array Les utilisateurs récupérés
     */
    public function recupererUtilisateursParEmail(string $email): array;

    /**
     * Fonction permettant de récupérer un utilisateur en fonction de son prénom et
     * de son nom
     * @return array  Les utilisateurs récupérés
     */
    public function recupererUtilisateursOrderedPrenomNom(): array;

    /**
     * Fonction permettant de récupérer un utilisateur avec une recherche
     * @param $recherche, La recherche
     * @return mixed  Les utilisateurs récupérés
     */
    public function recherche($recherche);

}