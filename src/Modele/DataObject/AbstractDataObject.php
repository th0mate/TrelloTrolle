<?php

namespace App\Trellotrolle\Modele\DataObject;

abstract class AbstractDataObject
{

    /**
     * Fonction permettant de construire un objet depuis un tableau de paramètres
     * @return array
     */
    public abstract function formatTableau(): array;

}
