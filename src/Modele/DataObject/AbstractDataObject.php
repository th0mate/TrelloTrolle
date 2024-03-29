<?php

namespace App\Trellotrolle\Modele\DataObject;

abstract class AbstractDataObject
{

    /**
     * @return array
     */
    public abstract function formatTableau(): array;

}
