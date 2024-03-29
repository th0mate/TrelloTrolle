<?php

namespace App\Trellotrolle\Service\Exception;
use App\Trellotrolle\Modele\DataObject\Tableau;
use Exception;

class TableauException extends ServiceException
{
    /**
     * @var Tableau
     */
    private Tableau $tableau;

    /**
     * @param $message
     * @param $tableau
     * @param $code
     */
    public function __construct($message, $tableau, $code)
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