<?php

namespace App\Trellotrolle\Tests;

use App\Trellotrolle\Configuration\ConfigurationBaseDeDonneesInterface;

class ConfigurationBDDTestUnitaires implements ConfigurationBaseDeDonneesInterface
{

    public function getLogin(): string
    {
        return "";
    }

    public function getNomBaseDeDonnees(): string
    {
        // TODO: Implement getNomBaseDeDonnees() method.
    }

    public function getPort(): string
    {
        // TODO: Implement getPort() method.
    }

    public function getNomHote(): string
    {
        // TODO: Implement getNomHote() method.
    }

    public function getMotDePasse(): string
    {
        return "";
    }

    public function getDSN()
    {
        return "sqlite:".__DIR__."/db_test.db";
    }

    public function getOptions()
    {
        return [];
    }
}