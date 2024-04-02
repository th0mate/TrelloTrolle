<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Configuration\ConfigurationBaseDeDonnees;
use App\Trellotrolle\Configuration\ConfigurationBaseDeDonneesInterface;
use PDO;


class ConnexionBaseDeDonnees implements ConnexionBaseDeDonneesInterface
{
    /**
     * ConnexionBaseDeDonnees constructor.
     * @var PDO $pdo le PDO
     */
    private PDO $pdo;

    /**
     * Fonction permettant de récupérer le PDO
     * @return PDO le PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * ConnexionBaseDeDonnees constructor.
     * @param ConfigurationBaseDeDonneesInterface $configurationBDD La configuration de la base de données
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