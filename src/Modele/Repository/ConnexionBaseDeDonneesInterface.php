<?php

namespace App\Trellotrolle\Modele\Repository;

use PDO;

interface ConnexionBaseDeDonneesInterface
{
    /**
     * Fonction permettant de récupérer le PDO
     * @return PDO le PDO
     */
    public function getPdo(): PDO;
}