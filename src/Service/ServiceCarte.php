<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Controleur\ControleurCarte;
use App\Trellotrolle\Controleur\ControleurColonne;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepositoryInterface;
use App\Trellotrolle\Service\Exception\CreationException;
use App\Trellotrolle\Service\Exception\MiseAJourException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;
use Symfony\Component\HttpFoundation\Response;

class ServiceCarte implements ServiceCarteInterface
{


    /**
     * ServiceCarte constructor.
     * @param CarteRepository $carteRepository Repository des cartes
     * @param UtilisateurRepository $utilisateurRepository Repository des utilisateurs
     * @param TableauRepository $tableauRepository Repository des tableaux
     */
    public function __construct(private CarteRepository       $carteRepository,
                                private UtilisateurRepository $utilisateurRepository,
                                private TableauRepository     $tableauRepository)
    {
    }


    /**
     * Fonction permettant de récupérer une carte par son id
     * @param int|null $idCarte L'id de la carte à récupérer
     * @return Carte La carte récupérée
     * @throws ServiceException Si le code de la carte est manquant
     * ou si la carte est inexistante
     */
    public function recupererCarte(?int $idCarte): Carte
    {

        if (is_null($idCarte)) {
            throw new ServiceException("Code de carte manquant", Response::HTTP_NOT_FOUND);
        }
        /**
         * @var Carte $carte
         */
        $carte = $this->carteRepository->recupererParClePrimaire($idCarte);
        if (!$carte) {
            throw new ServiceException("Carte inexistante", Response::HTTP_NOT_FOUND);
        }
        return $carte;
    }


    /**
     * Fonction permettant de supprimer une carte par son id
     * @param Tableau $tableau Le tableau auquel appartient la carte
     * @param $idCarte L'id de la carte à supprimer
     * @return array Les cartes du tableau après suppression de la carte donnée
     */
    public function supprimerCarte(Tableau $tableau, $idCarte): array
    {
        $this->carteRepository->supprimer($idCarte);
        return $this->carteRepository->recupererCartesTableau($tableau->getIdTableau());
    }


    /**
     * Fonction permettant de créer une carte
     * @param Tableau $tableau Le tableau dans lequel la carte va être créée
     * @param $attributs, Les attributs de la carte à créer
     * @param Colonne $colonne La colonne dans laquelle la carte va être créée
     * @return Carte La carte créée
     * @throws CreationException Si un membre à affecter n'existe pas ou
     * s'il n'est pas collaborateur du tableau
     */
    public function creerCarte(Tableau $tableau, $attributs, Colonne $colonne): Carte
    {
        $affectationsCarte = $attributs["affectationsCarte"];
        $affectations = [];
        if (!is_null($affectationsCarte)) {
            foreach ($affectationsCarte as $affectation) {
                /**
                 * @var Utilisateur $utilisateur
                 */
                $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($affectation);
                if (!$utilisateur) {
                    throw new CreationException("Un des membres affecté à la tâche n'existe pas", 404);
                }
                if (!$this->tableauRepository->estParticipantOuProprietaire($utilisateur->getLogin(),$tableau)) {
                    throw new CreationException("Un des membres affecté à la tâche n'est pas affecté au tableau.", 403);
                }
                $affectations[] = $utilisateur;
            }
        }
        $attributs["affectationsCarte"] = $affectations;
        return $this->newCarte($colonne, $attributs);
    }

    /**
     * Fonction permettant de créer une carte
     * @param Colonne $colonne La colonne dans laquelle la carte va être créée
     * @param $attributs, Les attributs de la carte à créer
     * @return Carte La carte créée
     */
    public function newCarte(Colonne $colonne, $attributs): Carte
     {
        $carte = new Carte(
            $this->carteRepository->getNextIdCarte(),
            $attributs["titreCarte"],
            $attributs["descriptifCarte"],
            $attributs["couleurCarte"],
            $colonne
        );
        $this->carteRepository->ajouter($carte);
        $this->carteRepository->setAffectationsCarte($attributs["affectationsCarte"],$carte);
        return $carte;
    }


    /**
     * Fonction qui récupère les attributs de la carte
     * @param array $attributs Les attributs à vérifier
     * @return void
     * @throws CreationException Si les attributs sont manquants
     */
    public function recupererAttributs(array $attributs): void
    {
        foreach ($attributs as $attribut) {
            if (is_null($attribut)) {
                throw new CreationException("Attributs manquants", 404);
            }
        }
    }


    /**
     * Fonction permettant de mettre à jour une carte
     * @param Tableau $tableau Le tableau auquel appartient la carte
     * @param $attributs, Les attributs de la carte à mettre à jour
     * @param Carte $carte La carte à mettre à jour
     * @param Colonne $colonne La colonne de la carte
     * @return Carte La carte mise à jour
     * @throws CreationException Si un membre à affecter n'existe pas
     * @throws MiseAJourException Si un membre affecté à la tâche n'est pas affecté au tableau
     */
    public function miseAJourCarte(Tableau $tableau, $attributs, Carte $carte, Colonne $colonne): Carte
    {
        $affectationsCarte = $attributs["affectationsCarte"];
        $affectations = [];
        if (!is_null($affectationsCarte)) {
            foreach ($affectationsCarte as $affectation) {
                /**
                 * @var Utilisateur $utilisateur
                 */
                $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($affectation);
                if (!$utilisateur) {
                    throw new CreationException("Un des membres affecté à la tâche n'existe pas", 404);
                }
                if (!$this->tableauRepository->estParticipantOuProprietaire($utilisateur->getLogin(),$tableau)) {
                    throw new MiseAJourException("Un des membres affecté à la tâche n'est pas affecté au tableau", "danger", 403);
                }
                $affectations[] = $utilisateur;
            }
        }
        $attributs["affectationsCarte"] = $affectations;
        $this->carteUpdate($carte, $colonne, $attributs);
        return $carte;
    }

    /**
     * Fonction permettant de mettre à jour une carte
     * @param Carte $carte La carte à mettre à jour
     * @param Colonne $colonne La colonne de la carte
     * @param $attributs, Les attributs de la carte à mettre à jour
     * @return Carte La carte mise à jour
     */
    public function carteUpdate(Carte $carte, Colonne $colonne, $attributs): Carte
    {
        $carte->setColonne($colonne);
        $carte->setTitreCarte($attributs["titreCarte"]);
        $carte->setDescriptifCarte($attributs["descriptifCarte"]);
        $carte->setCouleurCarte($attributs["couleurCarte"]);
        $this->carteRepository->setAffectationsCarte($attributs["affectationsCarte"], $carte);
        $this->carteRepository->mettreAJour($carte);
        return $carte;
    }


    /**
     * Fonction permettant de vérifier si une carte peut être mise à jour
     * @param $idCarte L'id de la carte à mettre à jour
     * @param Colonne $colonne La colonne de la carte
     * @param $attributs, attributs de la carte à mettre à jour
     * @return Carte La carte à mettre à jour
     * @throws CreationException Si le tableau de la colonne de la carte n'est pas
     * le même que celui dont la carte est issue
     */
    public function verificationsMiseAJourCarte(int $idCarte, Colonne $colonne, $attributs): Carte
    {
        $carte = $this->carteRepository->getAllFromTable($idCarte);
        $this->recupererAttributs($attributs);
        $originalColonne = $carte->getColonne();
        if ($originalColonne->getTableau()->getIdTableau() !== $colonne->getTableau()->getIdTableau()) {
            throw new CreationException("Le tableau de cette colonne n'est pas le même que celui de la colonne d'origine de la carte!", Response::HTTP_CONFLICT);
        }
        return $carte;
    }

    /**
     * Fonction permettant de mettre à jour les affectations d'une carte
     * @param Tableau $tableau Le tableau auquel appartient la carte
     * @param Utilisateur $utilisateur L'utilisateur à mettre à jour
     * @return void
     */
    public function miseAJourCarteMembre(Tableau $tableau, AbstractDataObject $utilisateur): void
   {
        $cartes = $this->carteRepository->recupererCartesTableau($tableau->getIdTableau());
        foreach ($cartes as $carte) {
            $affectations = array_filter($this->carteRepository->getAffectationsCarte($carte), function ($u) use ($utilisateur) {
                return $u->getLogin() != $utilisateur->getLogin();
            });
            $this->carteRepository->setAffectationsCarte($affectations, $carte);
        }
    }

    /**
     * Fonction permettant de récupérer le prochain id de carte
     * @return int L'id de la prochaine carte
     */
    public function getNextIdCarte(): int
    {
        return $this->carteRepository->getNextIdCarte();
    }

    /**
     * Fonction permettant de déplacer une carte
     * @param Carte $carte La carte à déplacer
     * @param Colonne $colonne La colonne dans laquelle la carte va être déplacée
     * @return void
     */
    public function deplacerCarte(Carte $carte, Colonne $colonne): void
    {
        $carte->setColonne($colonne);
        $this->carteRepository->mettreAJour($carte);
    }

    /**
     * Fonction permettant de récupérer les affectations d'une carte
     * @param Carte $carte La carte dont on veut récupérer les affectations
     * @return array Les affectations de la carte
     */
    public function getAffectations(Carte $carte) :array
    {
        return $this->carteRepository->getAffectationsCarte($carte);
    }
}