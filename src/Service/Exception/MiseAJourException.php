<?php

namespace App\Trellotrolle\Service\Exception;

class MiseAJourException extends ServiceException
{

    /**
     * Le type de message flash
     * @var
     */
    private $typeMessageFlash;

    /**
     * MiseAJourException constructor.
     * @param string $message Le message d'erreur
     * @param $typeMessageFlash, Le type de message flash
     * @param $code, Le code d'erreur
     */
    public function __construct(string $message, $typeMessageFlash, $code)
    {
        parent::__construct($message,$code);
        $this->typeMessageFlash=$typeMessageFlash;
    }

    /**
     * Fonction permettant de récupérer le type de message flash
     * @return mixed
     */
    public function getTypeMessageFlash()
    {
        return $this->typeMessageFlash;
    }
}