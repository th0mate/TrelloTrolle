<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Controleur\ControleurCarte;
use App\Trellotrolle\Controleur\ControleurColonne;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\CarteRepositoryInterface;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepositoryInterface;
use App\Trellotrolle\Service\Exception\CreationException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;
use Symfony\Component\HttpFoundation\Response;

class ServiceColonne implements ServiceColonneInterface
{


    /**
     * @param ColonneRepositoryInterface $colonneRepository
     */
    public function __construct(private ColonneRepositoryInterface $colonneRepository)
    {
    }


    /**
     * @param $idColonne
     * @return Colonne
     * @throws ServiceException
     */
    public function recupererColonne($idColonne): Colonne
    {
        if (is_null($idColonne)) {
            throw new ServiceException("Identifiant de colonne manquant",404);
        }

        /**
         * @var Colonne $colonne
         **/
        $colonne = $this->colonneRepository->recupererParClePrimaire(strval($idColonne));
        if (!$colonne) {
            throw new ServiceException("Colonne inexistante",404);
        }
        return $colonne;
    }

    /**
     * @param $idTableau
     * @return array
     */
    public function recupererColonnesTableau($idTableau): array
    {
        return $this->colonneRepository->recupererColonnesTableau($idTableau);
    }


    /**
     * @param Tableau $tableau
     * @param $idColonne
     * @return array
     */
    public function supprimerColonne(Tableau $tableau, $idColonne): array
    {
        $this->colonneRepository->supprimer($idColonne);
        return $this->colonneRepository->recupererColonnesTableau($tableau->getIdTableau());

    }


    /**
     * @param $nomColonne
     * @return void
     * @throws CreationException
     */
    public function isSetNomColonne($nomColonne): void
    {
        if (is_null($nomColonne)) {
            throw new CreationException("Nom de colonne manquant",Response::HTTP_NOT_FOUND);
        }
    }


    /**
     * @param $idColonne
     * @param $nomColonne
     * @return Colonne
     * @throws CreationException
     * @throws ServiceException
     */
    public function recupererColonneAndNomColonne($idColonne, $nomColonne): Colonne
    {
        $colonne = $this->recupererColonne($idColonne);
        $this->isSetNomColonne($nomColonne);
        return $colonne;
    }

    /**
     * @param Tableau $tableau
     * @param $nomColonne
     * @return Colonne
     */
    public function creerColonne(Tableau $tableau, $nomColonne): Colonne
    {
        $colonne= new Colonne(
            $this->colonneRepository->getNextIdColonne(),
            $nomColonne,
            $tableau
        );
        $this->colonneRepository->ajouter($colonne);
        return $colonne;
    }

    /**
     * @param Colonne $colonne
     * @return Colonne
     */
    public function miseAJourColonne(Colonne $colonne): Colonne
    {
        $this->colonneRepository->mettreAJour($colonne);
        return $colonne;
    }

    /**
     * @return int
     */
    public function getNextIdColonne(): int
    {
        return $this->colonneRepository->getNextIdColonne();
    }

    /**
     * @param $idColonne1
     * @param $idColonne2
     * @return void
     */
    public function inverserOrdreColonnes($idColonne1, $idColonne2): void
    {
        $this->colonneRepository->inverserOrdreColonnes($idColonne1, $idColonne2);
    }
}