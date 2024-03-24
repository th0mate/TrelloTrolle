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
use App\Trellotrolle\Modele\Repository\CarteRepositoryInterface;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepositoryInterface;
use App\Trellotrolle\Service\Exception\CreationException;
use App\Trellotrolle\Service\Exception\MiseAJourException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;
use Symfony\Component\HttpFoundation\Response;

class ServiceCarte implements ServiceCarteInterface
{


    public function __construct(private CarteRepositoryInterface $carteRepository,
                                private UtilisateurRepositoryInterface $utilisateurRepository)
    {}


    public function recupererCarte(?int $idCarte): Carte
    {
        //TODO différencier warning de danger pour message flash:
        //Pk ici c'est différent mais pas pour recupererColonne, voir afficherFormulaireCreationCarte
        //Dans la fonction mettreAJourCarte(), les deux sont des warnings contrairement à supprimerCarte()
        if (is_null($idCarte)) {
            //warning
            throw new ServiceException("Code de carte manquant",Response::HTTP_NOT_FOUND);
        }
        /**
         * @var Carte $carte
         */
        $carte = $this->carteRepository->recupererParClePrimaire($idCarte);
        if (!$carte) {
            //danger
            throw new ServiceException("Carte inexistante",Response::HTTP_NOT_FOUND);
        }
        return $carte;
    }

    public function supprimerCarte(int $idCarte): void
    {
        $this->carteRepository->supprimer($idCarte);
    }

    public function creerCarte(Tableau $tableau,array $attributs,Colonne $colonne):Carte
    {
        $affectationsCarte=$attributs["affectationsCarte"];
        $affectations = [];
        if (!is_null($affectationsCarte)) {
            foreach ($affectationsCarte as $affectation) {
                /**
                 * @var Utilisateur $utilisateur
                 */
                $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($affectation);
                if (!$utilisateur) {
                    throw new CreationException("Un des membres affecté à la tâche n'existe pas",404);
                }
                if (!$tableau->estParticipantOuProprietaire($utilisateur->getLogin())) {
                    throw new CreationException("Un des membres affecté à la tâche n'est pas affecté au tableau.",400);
                }
                $affectations[] = $utilisateur;
            }
        }
        $attributs["affectationsCarte"]=$affectations;
        $carte=new Carte(
            $this->carteRepository->getNextIdCarte(),
            $attributs["titreCarte"],
            $attributs["descriptifCarte"],
            $attributs["couleurCarte"],
            $colonne
        );
        $this->carteRepository->ajouter($carte);
        return $carte;
    }

    public function recupererAttributs(array $attributs): void
    {
        foreach ($attributs as $attribut) {
            if (is_null($attribut)) {
                throw new CreationException("Attributs manquants",404);
            }
        }
    }

    /**
     * @throws CreationException
     * @throws MiseAJourException
     */
    public function miseAJourCarte($tableau, $attributs, $carte, $colonne)
    {
        $affectationsCarte=$attributs["affectationsCarte"];
        $affectations=[];
        if (!is_null($affectationsCarte)) {
            foreach ($affectationsCarte as $affectation) {
                /**
                 * @var Utilisateur $utilisateur
                 */
                $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($affectation);
                if (!$utilisateur) {
                    throw new CreationException("Un des membres affecté à la tâche n'existe pas",404);
                }
                if (!$tableau->estParticipantOuProprietaire($utilisateur->getLogin())) {
                    throw new MiseAJourException("Un des membres affecté à la tâche n'est pas affecté au tableau","danger",400);
                }
                $affectations[] = $utilisateur;
            }
        }
        $attributs["affectationsCarte"]=$affectations;
        $this->carteUpdate($carte,$colonne,$attributs);
        return $carte;
    }

    public function carteUpdate(Carte $carte,$colonne,$attributs): Carte
    {
        $carte->setColonne($colonne);
        $carte->setTitreCarte($attributs["titreCarte"]);
        $carte->setDescriptifCarte($attributs["descriptifCarte"]);
        $carte->setCouleurCarte($attributs["couleurCarte"]);
        $this->carteRepository->setAffectationsCarte($attributs["affectationsCarte"],$carte);
        $this->carteRepository->mettreAJour($carte);
        return $carte;
    }

    /**
     * @throws CreationException
     * @throws ServiceException
     */
    public function verificationsMiseAJourCarte($idCarte, $colonne,$attributs)
    {
        $carte=$this->recupererCarte($idCarte);
        $this->recupererAttributs($attributs);
        $originalColonne = $carte->getColonne();
        if ($originalColonne->getTableau()->getIdTableau() !== $colonne->getTableau()->getIdTableau()) {
            throw new CreationException("Le tableau de cette colonne n'est pas le même que celui de la colonne d'origine de la carte!", Response::HTTP_CONFLICT);
        }

        return $carte;
    }

    public function miseAJourCarteMembre($tableau,$utilisateur)
    {
        $cartes = $this->carteRepository->recupererCartesTableau($tableau->getIdTableau());
        foreach ($cartes as $carte) {
            $affectations = array_filter($carte->getAffectationsCarte(), function ($u) use ($utilisateur) {
                return $u->getLogin() != $utilisateur->getLogin();
            });
            $carte->setAffectationsCarte($affectations);
            $this->carteRepository->mettreAJour($carte);
        }
    }

    public function getNextIdCarte()
    {
        return $this->carteRepository->getNextIdCarte();
    }
}