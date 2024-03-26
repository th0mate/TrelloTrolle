<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Controleur\ControleurCarte;
use App\Trellotrolle\Controleur\ControleurColonne;
use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Service\Exception\CreationException;
use App\Trellotrolle\Service\Exception\MiseAJourException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;
use Symfony\Component\HttpFoundation\Response;

class ServiceCarte implements ServiceCarteInterface
{


    public function __construct(private CarteRepository       $carteRepository,
                                private UtilisateurRepository $utilisateurRepository,
                                private TableauRepository     $tableauRepository)
    {
    }

    /**
     * @throws ServiceException
     */
    public function recupererCarte($idCarte): Carte
    {
        //TODO différencier warning de danger pour message flash:
        //Pk ici c'est différent mais pas pour recupererColonne, voir afficherFormulaireCreationCarte
        //Dans la fonction mettreAJourCarte(), les deux sont des warnings contrairement à supprimerCarte()
        if (is_null($idCarte)) {
            //warning
            throw new ServiceException("Code de carte manquant", Response::HTTP_NOT_FOUND);
        }
        /**
         * @var Carte $carte
         */
        $carte = $this->carteRepository->recupererParClePrimaire($idCarte);
        if (!$carte) {
            //danger
            throw new ServiceException("Carte inexistante", Response::HTTP_NOT_FOUND);
        }
        return $carte;
    }

    /**
     * @throws TableauException
     */
    public function supprimerCarte(Tableau $tableau, $idCarte): array
    {
        $this->carteRepository->supprimer($idCarte);
        return $this->carteRepository->recupererCartesTableau($tableau->getIdTableau());
    }

    /**
     * @throws CreationException
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
                    throw new CreationException("Un des membres affecté à la tâche n'est pas affecté au tableau.", 400);
                }
                $affectations[] = $utilisateur;
            }
        }
        $attributs["affectationsCarte"] = $affectations;
        return $this->newCarte($colonne, $attributs);
    }

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
     * @throws CreationException
     */
    public function recupererAttributs($attributs): void
    {
        foreach ($attributs as $attribut) {
            if (is_null($attribut)) {
                throw new CreationException("Attributs manquants", 404);
            }
        }
    }

    /**
     * @throws CreationException
     * @throws MiseAJourException
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
                    throw new MiseAJourException("Un des membres affecté à la tâche n'est pas affecté au tableau", "danger", 400);
                }
                $affectations[] = $utilisateur;
            }
        }
        $attributs["affectationsCarte"] = $affectations;
        $this->carteUpdate($carte, $colonne, $attributs);
        return $carte;
    }

    public function carteUpdate(Carte $carte, $colonne, $attributs): Carte
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
     * @throws CreationException
     * @throws ServiceException
     */
    public function verificationsMiseAJourCarte($idCarte, Colonne $colonne, $attributs): Carte
    {
        $carte = $this->recupererCarte($idCarte);
        $this->recupererAttributs($attributs);
        $originalColonne = $carte->getColonne();
        if ($originalColonne->getTableau()->getIdTableau() !== $colonne->getTableau()->getIdTableau()) {
            throw new CreationException("Le tableau de cette colonne n'est pas le même que celui de la colonne d'origine de la carte!", Response::HTTP_CONFLICT);
        }

        return $carte;
    }

    public function miseAJourCarteMembre(Tableau $tableau, Utilisateur $utilisateur): void
    {
        $cartes = $this->carteRepository->recupererCartesTableau($tableau->getIdTableau());
        foreach ($cartes as $carte) {
            $affectations = array_filter($carte->getAffectationsCarte(), function ($u) use ($utilisateur) {
                return $u->getLogin() != $utilisateur->getLogin();
            });
            $this->carteRepository->setAffectationsCarte($affectations, $carte);
            $this->carteRepository->mettreAJour($carte);
        }
    }

    public function getNextIdCarte(): int
    {
        return $this->carteRepository->getNextIdCarte();
    }

    public function deplacerCarte(Carte $carte,Colonne $colonne): void
    {
        $carte->setColonne($colonne);
        $this->carteRepository->mettreAJour($carte);
    }
}