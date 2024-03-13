<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Controleur\ControleurCarte;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Service\Exception\ServiceException;

class ServiceColonne
{

    private ColonneRepository $colonneRepository;

    public function __construct()
    {
        $this->colonneRepository = new ColonneRepository();
    }

    /**
     * @throws ServiceException
     */
    public function recupererColonne(): Colonne
    {
        if (!ControleurCarte::issetAndNotNull(["idColonne"])) {
            throw new ServiceException("Identifiant de colonne manquant");
        }
        $colonneRepository = new ColonneRepository();

        /**
         * @var Colonne $colonne
         **/

        $colonne = $colonneRepository->recupererParClePrimaire($_REQUEST["idColonne"]);
        if (!$colonne) {
            throw new ServiceException("Colonne inexistante");
        }
        return $colonne;
    }

    public function recupererColonnesTableau($idTableau):array
    {
        return $this->colonneRepository->recupererColonnesTableau($idTableau);
    }
}