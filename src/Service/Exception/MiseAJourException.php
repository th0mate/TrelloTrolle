<?php

namespace App\Trellotrolle\Service\Exception;

class MiseAJourException extends ServiceException
{

    private $typeMessageFlash;

    public function __construct(string $message,$typeMessageFlash)
    {
        parent::__construct($message);
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