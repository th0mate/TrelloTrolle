<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Controleur\ControleurCarte;
use App\Trellotrolle\Controleur\ControleurColonne;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Service\Exception\CreationException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;
use Symfony\Component\HttpFoundation\Response;

class ServiceColonne implements ServiceColonneInterface
{


    public function __construct(private ColonneRepository $colonneRepository,
                                private CarteRepository   $carteRepository)
    {
    }

    /**
     * @throws ServiceException
     */
    public function recupererColonne(int $idColonne): Colonne
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

    public function recupererColonnesTableau($idTableau): array
    {
        return $this->colonneRepository->recupererColonnesTableau($idTableau);
    }

    /**
     * @throws TableauException
     */
    public function supprimerColonne($tableau, $idColonne): void
    {
        //TODO supprimer Vérif après refonte BD

        $this->colonneRepository->supprimer($idColonne);;
    }

    /**
     * @throws CreationException
     */
    public function isSetNomColonne($nomColonne): void
    {
        if (is_null($nomColonne)) {
            throw new CreationException("Nom de colonne manquant",Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @throws CreationException
     * @throws ServiceException
     */
    public function recupererColonneAndNomColonne($idColonne, $nomColonne)
    {
        $colonne = $this->recupererColonne($idColonne);
        $this->isSetNomColonne($nomColonne);
        return $colonne;
    }

    public function creerColonne($tableau, $nomColonne): Colonne
    {
        $colonne= new Colonne(
            $this->colonneRepository->getNextIdColonne(),
            $nomColonne,
            $tableau
        );
        $this->colonneRepository->ajouter($colonne);
        return $colonne;
    }

    public function miseAJourColonne($colonne): Colonne
    {
        $this->colonneRepository->mettreAJour($colonne);
        return $colonne;
    }
    public function getNextIdColonne()
    {
        return $this->colonneRepository->getNextIdColonne();
    }

    public function inverserOrdreColonnes($colonne1, $colonne2): void
    {
        $this->colonneRepository->inverserOrdreColonnes($colonne1, $colonne2);
    }
}