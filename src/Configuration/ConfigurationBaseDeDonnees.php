<?php

namespace App\Trellotrolle\Configuration;

class ConfigurationBaseDeDonnees implements ConfigurationBaseDeDonneesInterface
{

    private array $configurationBaseDeDonnees = array(
        'nomHote' => '162.38.222.142',
        'nomBaseDeDonnees' => 'iut',
        'port' => '5673',
        'login' => 'loyet',
        'motDePasse' => 'marine2022'
    );

    public function getLogin() : string {
        return $this->configurationBaseDeDonnees['login'];
    }

    public function getNomBaseDeDonnees() : string {
        return $this->configurationBaseDeDonnees['nomBaseDeDonnees'];
    }

    public function getPort() : string {
        return $this->configurationBaseDeDonnees['port'];
    }

    public function getNomHote() : string {
        return $this->configurationBaseDeDonnees['nomHote'];
    }

    public function getMotDePasse() : string {
        return $this->configurationBaseDeDonnees['motDePasse'];
    }
    public function getDSN()
    {
        return "pgsql:host=162.38.222.142;port=5673;dbname=iut";

    }

    public function getOptions()
    {
        return [];
    }

}