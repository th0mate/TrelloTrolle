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
use App\Trellotrolle\Service\Exception\CreationCarteException;
use App\Trellotrolle\Service\Exception\MiseAJourCarteException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;

class ServiceCarte
{

    private CarteRepository $carteRepository;
    private UtilisateurRepository $utilisateurRepository;

    public function __construct()
    {
        $this->carteRepository = new CarteRepository();
        $this->utilisateurRepository = new UtilisateurRepository();
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
            throw new ServiceException("Code de carte manquant");
        }
        $carteRepository = new CarteRepository();
        /**
         * @var Carte $carte
         */
        $carte = $carteRepository->recupererParClePrimaire($idCarte);
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
        if ($this->carteRepository->getNombreCartesTotalUtilisateur($tableau->getUtilisateur()->getLogin()) == 1) {
            throw new TableauException("Vous ne pouvez pas supprimer cette carte car cela entrainera la supression du compte du propriétaire du tableau", $tableau);
        }
        $this->carteRepository->supprimer($idCarte);
        return $this->carteRepository->recupererCartesTableau($tableau->getIdTableau());
    }

    /**
     * @throws CreationCarteException
     */
    public function creerCarte($tableau, $affectationsCarte): array
    {

        $affectations = [];
        if (!is_null($affectationsCarte)) {
            foreach ($affectationsCarte as $affectation) {
                /**
                 * @var Utilisateur $utilisateur
                 */
                $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($affectation);
                if (!$utilisateur) {
                    throw new CreationCarteException("Un des membres affecté à la tâche n'existe pas");
                }
                if (!$tableau->estParticipantOuProprietaire($utilisateur->getLogin())) {
                    throw new CreationCarteException("Un des membres affecté à la tâche n'est pas affecté au tableau.");
                }
                $affectations[] = $utilisateur;
            }
        }
        return $affectations;
    }

    public function newCarte($colonne,$titreCarte,$descCarte,$couleurCarte,$affectations):void
    {
        $carte=new Carte(
            $colonne,
            $this->carteRepository->getNextIdCarte(),
            $titreCarte,
            $descCarte,
            $couleurCarte,
            $affectations
        );
        $this->carteRepository->ajouter($carte);

    }

    /**
     * @throws CreationCarteException
     */
    public function recupererAttributs($attributs): void
    {
        foreach ($attributs as $attribut) {
            if (is_null($attribut)) {
                throw new CreationCarteException("Attributs manquants");
            }
        }
    }

    public function miseAJourCarte($tableau,$affectationsCarte):array
    {
        $affectations=[];
        if (!is_null($affectationsCarte)) {
            foreach ($affectationsCarte as $affectation) {
                /**
                 * @var Utilisateur $utilisateur
                 */
                $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($affectation);
                if (!$utilisateur) {
                    throw new CreationCarteException("Un des membres affecté à la tâche n'existe pas");
                }
                if (!$tableau->estParticipantOuProprietaire($utilisateur->getLogin())) {
                    throw new MiseAJourCarteException("Un des membres affecté à la tâche n'est pas affecté au tableau");
                }
                $affectations[] = $utilisateur;
            }
        }
        return $affectations;
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
     * @throws CreationCarteException
     */
    public function verifs($carte, $colonne): void
    {
        $originalColonne = $carte->getColonne();
        if ($originalColonne->getTableau()->getIdTableau() !== $colonne->getTableau()->getIdTableau()) {
            throw new CreationCarteException("Le tableau de cette colonne n'est pas le même que celui de la colonne d'origine de la carte!");
        }
    }
}