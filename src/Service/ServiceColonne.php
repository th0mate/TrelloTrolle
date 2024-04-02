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
     * ServiceColonne constructor.
     * @param ColonneRepositoryInterface $colonneRepository Repository des colonnes
     */
    public function __construct(private ColonneRepositoryInterface $colonneRepository)
    {
    }


    /**
     * Fonction permettant de récupérer une colonne par son id
     * @param $idColonne L'id de la colonne à récupérer
     * @return Colonne La colonne récupérée
     * @throws ServiceException Si l'identifiant de la colonne est manquant ou si la colonne est inexistante
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
     * Fonction permettant de récupérer les colonnes d'un tableau
     * @param $idTableau L'id du tableau dont on veut récupérer les colonnes
     * @return array Les colonnes du tableau
     */
    public function recupererColonnesTableau($idTableau): array
    {
        return $this->colonneRepository->recupererColonnesTableau($idTableau);
    }


    /**
     * Fonction permettant de supprimer une colonne
     * @param Tableau $tableau Le tableau dont on veut supprimer la colonne
     * @param $idColonne L'id de la colonne à supprimer
     * @return array Les colonnes du tableau après suppression de la colonne donnée
     */
    public function supprimerColonne(Tableau $tableau, $idColonne): array
    {
        $this->colonneRepository->supprimer($idColonne);
        return $this->colonneRepository->recupererColonnesTableau($tableau->getIdTableau());

    }


    /**
     * Fonction permettant de vérifier si une colonne est null via son nom
     * @param $nomColonne, Le nom de la colonne à vérifier
     * @return void
     * @throws CreationException Si le nom de la colonne est manquant
     */
    public function isSetNomColonne($nomColonne): void
    {
        if (is_null($nomColonne)) {
            throw new CreationException("Nom de colonne manquant",Response::HTTP_NOT_FOUND);
        }
    }


    /**
     * Fonction permettant de récupérer une colonne par son id et son nom
     * @param $idColonne L'id de la colonne à récupérer
     * @param $nomColonne, Le nom de la colonne à récupérer
     * @return Colonne La colonne récupérée
     * @throws CreationException Si le nom de la colonne est manquant
     * @throws ServiceException Si l'identifiant de la colonne est manquant ou si la colonne est inexistante
     */
    public function recupererColonneAndNomColonne($idColonne, $nomColonne): Colonne
    {
        $colonne = $this->recupererColonne($idColonne);
        $this->isSetNomColonne($nomColonne);
        return $colonne;
    }

    /**
     * Fonction permettant de créer une colonne
     * @param Tableau $tableau Le tableau sur lequel on veut créer la colonne
     * @param $nomColonne, Le nom de la colonne à créer
     * @return Colonne La colonne créée
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
     * Fonction permettant de mettre à jour une colonne
     * @param Colonne $colonne La colonne à mettre à jour
     * @return Colonne La colonne mise à jour
     */
    public function miseAJourColonne(Colonne $colonne): Colonne
    {
        $this->colonneRepository->mettreAJour($colonne);
        return $colonne;
    }

    /**
     * Fonction permettant de récupérer le prochain identifiant de colonne
     * @return int L'identifiant de la prochaine colonne
     */
    public function getNextIdColonne(): int
    {
        return $this->colonneRepository->getNextIdColonne();
    }

    /**
     * Fonction permettant d'inverser l'ordre de deux colonnes
     * @param $idColonne1 L'id de la première colonne
     * @param $idColonne2 L'id de la deuxième colonne
     * @return void
     */
    public function inverserOrdreColonnes($idColonne1, $idColonne2): void
    {
        $this->colonneRepository->inverserOrdreColonnes($idColonne1, $idColonne2);
    }
}