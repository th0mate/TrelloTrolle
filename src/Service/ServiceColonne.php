<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Controleur\ControleurCarte;
use App\Trellotrolle\Controleur\ControleurColonne;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Service\Exception\CreationCarteException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;

class ServiceColonne
{

    private ColonneRepository $colonneRepository;
    private CarteRepository $carteRepository;

    public function __construct()
    {
        $this->colonneRepository = new ColonneRepository();
        $this->carteRepository = new CarteRepository();

    }

    /**
     * @throws ServiceException
     */
    public function recupererColonne($idColonne): Colonne
    {
        if (is_null($idColonne)) {
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

    public function recupererColonnesTableau($idTableau): array
    {
        return $this->colonneRepository->recupererColonnesTableau($idTableau);
    }

    /**
     * @throws TableauException
     */
    public function supprimerColonne($tableau, $idColonne): int
    {
        if ($this->carteRepository->getNombreCartesTotalUtilisateur($tableau->getUtilisateur()->getLogin()) == 1) {
            throw new TableauException("Vous ne pouvez pas supprimer cette colonne car cela entrainera la suppression du compte du propriétaire du tableau", $tableau);
        }
        $this->colonneRepository->supprimer($idColonne);
        return $this->colonneRepository->getNombreColonnesTotalTableau($tableau->getIdTableau());
    }

    /**
     * @throws CreationCarteException
     */
    public function isSetNomColonne($nomColonne): void
    {
        if (is_null($nomColonne)) {
            throw new CreationCarteException("Nom de colonne manquant");
        }
    }

    public function creerColonne($tableau,$nomColonne): Colonne
    {
         return new Colonne(
            $tableau,
            $this->colonneRepository->getNextIdColonne(),
            $nomColonne
        );
    }
    public function miseAJourColonne($colonne): void
    {
        $this->colonneRepository->mettreAJour($colonne);
    }
}