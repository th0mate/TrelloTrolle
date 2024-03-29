<?php

namespace App\Trellotrolle\Configuration;

interface ConfigurationBaseDeDonneesInterface
{
    public function getLogin(): string;

    public function getNomBaseDeDonnees(): string;

    public function getPort(): string;

    public function getNomHote(): string;

    public function getMotDePasse(): string;
    public function getDSN();

    public function getOptions();
}