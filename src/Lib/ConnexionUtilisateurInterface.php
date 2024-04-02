<?php

namespace App\Trellotrolle\Lib;

interface ConnexionUtilisateurInterface
{
    /**
     * Fonction permettant de connecter un utilisateur
     * @param string $loginUtilisateur Le login de l'utilisateur à connecter
     * @return void
     */
    public function connecter(string $loginUtilisateur): void;

    /**
     * Fonction permettant de savoir si un utilisateur est connecté
     * @return bool Vrai si l'utilisateur est connecté, faux sinon
     */
    public function estConnecte(): bool;

    /**
     * Fonction permettant de déconnecter un utilisateur
     * @return void
     */
    public function deconnecter(): void;

    /**
     * Fonction permettant de récupérer le login de l'utilisateur connecté
     * @return string|null Le login de l'utilisateur connecté
     */
    public function getLoginUtilisateurConnecte(): ?string;

    /**
     * Fonction permettant de savoir si l'utilisateur connecté est celui
     * passé en paramètre
     * @param $login , le login de l'utilisateur
     * @return bool  Vrai si l'utilisateur connecté est celui passé en paramètre, faux sinon
     */
    public function estUtilisateur($login): bool;
}