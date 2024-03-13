<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Controleur\ControleurCarte;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;

class ServiceCarte
{

    private CarteRepository $carteRepository;

    public function __construct()
    {
        $this->carteRepository=new CarteRepository();
    }

    /**
     * @throws ServiceException
     */
    public function recupererCarte($idCarte): Carte
    {
        if(is_null($idCarte)) {
            throw new ServiceException("Code de carte manquant");
        }
        $carteRepository = new CarteRepository();
        /**
         * @var Carte $carte
         */
        $carte = $carteRepository->recupererParClePrimaire($idCarte);
        if(!$carte) {
            throw new ServiceException("Carte inexistante");
        }
        return $carte;
    }

    /**
     * @throws TableauException
     */
    public function supprimerCarte($tableau, $idCarte): array
    {
        if ($this->carteRepository->getNombreCartesTotalUtilisateur($tableau->getUtilisateur()->getLogin()) == 1) {
            throw new TableauException("Vous ne pouvez pas supprimer cette carte car cela entrainera la supression du compte du propriétaire du tableau",$tableau);
        }
        $this->carteRepository->supprimer($idCarte);
        return $this->carteRepository->recupererCartesTableau($tableau->getIdTableau());
    }
}