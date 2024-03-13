<?php

namespace App\Trellotrolle\Service\Exception;
use App\Trellotrolle\Modele\DataObject\Tableau;
use Exception;

class TableauException extends Exception
{
    private Tableau $tableau;

    public function __construct($message,$tableau)
    {
        parent::__construct($message);
        $this->tableau=$tableau;
    }

    /**
     * @return Tableau
     */
    public function getTableau(): Tableau
    {
        return $this->tableau;
    }
}