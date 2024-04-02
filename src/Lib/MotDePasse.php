<?php

namespace App\Trellotrolle\Lib;

use Exception;

class MotDePasse
{
    /**
     * @var string le poivre pour le hachage
     */
    private static string $poivre = "5QcWU25xA5XriYkC4HzgVN";

    /**
     * Fonction permettant de hacher un mot de passe
     * @param string $mdpClair Le mot de passe en clair
     * @return string Le mot de passe haché
     */
    public static function hacher(string $mdpClair): string
    {
        return MotDePasse::$poivre.hash('sha256', $mdpClair);
    }

    /**
     * Fonction permettant de vérifier un mot de passe
     * @param string $mdpClair Le mot de passe en clair
     * @param string $mdpHache Le mot de passe haché
     * @return bool Vrai si le mot de passe haché correspond au mot de passe en clair, faux sinon
     */
    public static function verifier(string $mdpClair, string $mdpHache): bool
    {
        return MotDePasse::hacher($mdpClair) === $mdpHache;
    }

    /**
     * Fonction permettant de générer une chaine de caractères aléatoire
     * @param int $nbCaracteres Le nombre de caractères de la chaine
     * @throws Exception si la génération de la chaine aléatoire échoue
     * @return string La chaine de caractères aléatoire
     */
    public static function genererChaineAleatoire(int $nbCaracteres = 22): string
    {
        // 22 caractères par défaut pour avoir au moins 128 bits aléatoires
        $octetsAleatoires = random_bytes(ceil($nbCaracteres * 6 / 8));
        return substr(base64_encode($octetsAleatoires), 0, $nbCaracteres);
    }
}