<?php

namespace App\Trellotrolle\Service\Exception;

class MiseAJourException extends ServiceException
{

    /**
     * @var
     */
    private $typeMessageFlash;

    /**
     * @param string $message
     * @param $typeMessageFlash
     * @param $code
     */
    public function __construct(string $message, $typeMessageFlash, $code)
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