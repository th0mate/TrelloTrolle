<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Controleur\ControleurCarte;
use App\Trellotrolle\Controleur\ControleurColonne;
use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Service\Exception\CreationException;
use App\Trellotrolle\Service\Exception\MiseAJourException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;

class ServiceCarte
{


    public function __construct(private CarteRepository $carteRepository,
                                private UtilisateurRepository $utilisateurRepository)
    {}

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
            throw new ServiceException("Code de carte manquant");
        }
        /**
         * @var Carte $carte
         */
        $carte = $this->carteRepository->recupererParClePrimaire($idCarte);
        if (!$carte) {
            //danger
            throw new ServiceException("Carte inexistante");
        }
        return $carte;
    }

    /**
     * @throws TableauException
     */
    public function supprimerCarte($tableau, $idCarte): array
    {
        //TODO supprimer Vérif après refonte BD
        if ($this->carteRepository->getNombreCartesTotalUtilisateur($tableau->getUtilisateur()->getLogin()) == 1) {
            throw new TableauException("Vous ne pouvez pas supprimer cette carte car cela entrainera la supression du compte du propriétaire du tableau", $tableau);
        }
        $this->carteRepository->supprimer($idCarte);
        return $this->carteRepository->recupererCartesTableau($tableau->getIdTableau());
    }

    /**
     * @throws CreationException
     */
    public function creerCarte($tableau, $attributs,$colonne)
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
                    throw new CreationException("Un des membres affecté à la tâche n'existe pas");
                }
                if (!$tableau->estParticipantOuProprietaire($utilisateur->getLogin())) {
                    throw new CreationException("Un des membres affecté à la tâche n'est pas affecté au tableau.");
                }
                $affectations[] = $utilisateur;
            }
        }
        $attributs["affectationsCarte"]=$affectations;
        $this->newCarte($colonne,$attributs);
    }

    public function newCarte($colonne,$attributs):void
    {
        $carte=new Carte(
            $colonne,
            $this->carteRepository->getNextIdCarte(),
            $attributs["titreCarte"],
            $attributs["descriptifCarte"],
            $attributs["couleurCarte"],
            $attributs["affectationsCarte"]
        );
        $this->carteRepository->ajouter($carte);

    }

    /**
     * @throws CreationException
     */
    public function recupererAttributs($attributs): void
    {
        foreach ($attributs as $attribut) {
            if (is_null($attribut)) {
                throw new CreationException("Attributs manquants");
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
                    throw new CreationException("Un des membres affecté à la tâche n'existe pas");
                }
                if (!$tableau->estParticipantOuProprietaire($utilisateur->getLogin())) {
                    throw new MiseAJourException("Un des membres affecté à la tâche n'est pas affecté au tableau","danger");
                }
                $affectations[] = $utilisateur;
            }
        }
        $attributs["affectationsCarte"]=$affectations;
        $this->carteUpdate($carte,$colonne,$attributs);
    }

    public function carteUpdate(Carte $carte,$colonne,$attributs): void
    {
        $carte->setColonne($colonne);
        $carte->setTitreCarte($attributs["titreCarte"]);
        $carte->setDescriptifCarte($attributs["descriptifCarte"]);
        $carte->setCouleurCarte($attributs["couleurCarte"]);
        $carte->setAffectationsCarte($attributs["affectationsCarte"]);
        $this->carteRepository->mettreAJour($carte);
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
            throw new CreationException("Le tableau de cette colonne n'est pas le même que celui de la colonne d'origine de la carte!");
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
}