<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Configuration\ConfigurationBaseDeDonnees;
use PDO;


class ConnexionBaseDeDonnees
{
    private PDO $pdo;

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    private function __construct(ConfigurationBaseDeDonnees $configurationBaseDeDonnees)
    {
        $nomHote = $configurationBaseDeDonnees->getNomHote();
        $port = $configurationBaseDeDonnees->getPort();
        $login = $configurationBaseDeDonnees->getLogin();
        $motDePasse = $configurationBaseDeDonnees->getMotDePasse();
        $nomBaseDeDonnees = $configurationBaseDeDonnees->getNomBaseDeDonnees();

        $this->pdo = new PDO(
            "pgsql:host=$nomHote;port=$port;dbname=$nomBaseDeDonnees",
            $login,
            $motDePasse,
            array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
        );

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

}