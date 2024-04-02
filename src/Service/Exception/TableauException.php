<?php

namespace App\Trellotrolle\Service\Exception;
use App\Trellotrolle\Modele\DataObject\Tableau;
use Exception;

class TableauException extends ServiceException
{
    /**
     * Variable contenant le tableau
     * @var Tableau
     */
    private Tableau $tableau;

    /**
     * TableauException constructor.
     * @param $message, Le message d'erreur
     * @param $tableau, Le tableau
     * @param $code, Le code d'erreur
     */
    public function __construct($message, $tableau, $code)
    {
        parent::__construct($message,$code);
        $this->tableau=$tableau;
    }


    /**
     * Fonction permettant de récupérer le tableau
     * @return Tableau Le tableau récupéré
     */
    public function getTableau(): Tableau
    {
        return $this->tableau;
    }
}