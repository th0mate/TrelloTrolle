<?php

namespace App\Trellotrolle\Lib;

interface ConnexionUtilisateurInterface
{
    public function connecter(string $loginUtilisateur): void;

    public function estConnecte(): bool;

    public function deconnecter(): void;

    public function getLoginUtilisateurConnecte(): ?string;

    public function estUtilisateur($login): bool;
}