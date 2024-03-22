<?php

namespace App\Trellotrolle\Service\Exception;
use App\Trellotrolle\Modele\DataObject\Tableau;
use Exception;

class TableauException extends ServiceException
{
    private Tableau $tableau;

    public function __construct($message,$tableau,$code)
    {
        parent::__construct($message,$code);
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