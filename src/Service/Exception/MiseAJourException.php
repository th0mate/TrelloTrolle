<?php

namespace App\Trellotrolle\Service\Exception;

class MiseAJourException extends ServiceException
{

    private $typeMessageFlash;

    public function __construct(string $message,$typeMessageFlash,$code)
    {
        parent::__construct($message,$code);
        $this->typeMessageFlash=$typeMessageFlash;
    }

    /**
     * @return mixed
     */
    public function getTypeMessageFlash()
    {
        return $this->typeMessageFlash;
    }
}