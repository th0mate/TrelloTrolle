<?php

namespace App\Trellotrolle\Modele\DataObject;

abstract class AbstractDataObject
{

    /**
     * Fonction permettant de construire un objet depuis un tableau de paramètres
     * @return array Le tableau de paramètres
     */
    public abstract function formatTableau(): array;

}
