<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Configuration\ConfigurationBaseDeDonnees;
use App\Trellotrolle\Configuration\ConfigurationBaseDeDonneesInterface;
use PDO;


class ConnexionBaseDeDonnees implements ConnexionBaseDeDonneesInterface
{
    /**
     * @var PDO
     */
    private PDO $pdo;

    /**
     * @return PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * @param ConfigurationBaseDeDonneesInterface $configurationBDD
     */
    public function __construct(ConfigurationBaseDeDonneesInterface $configurationBDD)
    {

        $this->pdo = new PDO(
            $configurationBDD->getDSN(),
            $configurationBDD->getLogin(),
            $configurationBDD->getMotDePasse(),
            $configurationBDD->getOptions()
        );

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

}