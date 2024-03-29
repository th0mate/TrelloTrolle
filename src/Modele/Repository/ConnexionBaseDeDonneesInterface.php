<?php

namespace App\Trellotrolle\Modele\Repository;

use PDO;

interface ConnexionBaseDeDonneesInterface
{
    /**
     * @return PDO
     */
    public function getPdo(): PDO;
}