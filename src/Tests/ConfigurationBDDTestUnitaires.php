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

    }

    public function getPort(): string
    {

    }

    public function getNomHote(): string
    {

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