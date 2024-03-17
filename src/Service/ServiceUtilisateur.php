<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Controleur\ControleurCarte;
use App\Trellotrolle\Controleur\ControleurTableau;
use App\Trellotrolle\Controleur\ControleurUtilisateur;
use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;

class ServiceUtilisateur
{
    private UtilisateurRepository $utilisateurRepository;

    private TableauRepository $tableauRepository;

    public function __construct()
    {
        $this->utilisateurRepository = new UtilisateurRepository();
        $this->tableauRepository = new TableauRepository();
    }

    /**
     * @throws TableauException
     */
    public function estParticipant($tableau)
    {
        //TODO fonctions et appels à revoir car message de messageFlash différents
        if (!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            throw new TableauException("Vous n'avez pas de droits d'éditions sur ce tableau", $tableau);
        }
    }

    public function recupererUtilisateurParCle($login): \App\Trellotrolle\Modele\DataObject\AbstractDataObject
    {
        return $this->utilisateurRepository->recupererParClePrimaire($login);
    }

    /**
     * @throws TableauException
     */
    public function estProprietaire(Tableau $tableau, $login)
    {
        if (!$tableau->estProprietaire($login)) {
            throw new TableauException("Vous n'êtes pas propriétaire de ce tableau", $tableau);
        }
    }

    /**
     * @throws TableauException
     */
    public function isNotNullLogin($login, $tableau, $action)
    {
        if (is_null($login)) {
            throw new TableauException("Login du membre à " . $action . " manquant", $tableau);
        }
    }

    /**
     * @throws TableauException
     */
    public function utilisateurExistant($login, $tableau)
    {
        $utilisateur = $this->recupererUtilisateurParCle($login);
        if (!$utilisateur) {
            throw new TableauException("Utilisateur inexistant", $tableau);
        }
        return $utilisateur;
    }

    /**
     * @throws TableauException
     */
    public function ajouterMembre(Tableau $tableau, mixed $login)
    {
        $this->estProprietaire($tableau, ConnexionUtilisateur::getLoginUtilisateurConnecte());
        $this->isNotNullLogin($login, $tableau, "ajouter");
        $utilisateur = $this->utilisateurExistant($login, $tableau);
        if ($tableau->estParticipantOuProprietaire($login)) {
            throw new TableauException("Ce membre est déjà membre du tableau", $tableau);
        }

        $participants = $tableau->getParticipants();
        $participants[] = $utilisateur;
        $tableau->setParticipants($participants);
        $this->tableauRepository->mettreAJour($tableau);

    }

    /**
     * @throws TableauException
     */
    public function supprimerMembre(Tableau $tableau, $login)
    {
        $this->estProprietaire($tableau,ConnexionUtilisateur::getLoginUtilisateurConnecte());
        $this->isNotNullLogin($login,$tableau,"supprimer");
        $utilisateur=$this->utilisateurExistant($login,$tableau);
        if($tableau->estProprietaire($login)){
            throw new TableauException("Vous ne pouvez pas vous supprimer du tableau.",$tableau);
        }
        if (!$tableau->estParticipant($utilisateur->getLogin())){
            throw new TableauException("Cet utilisateur n'est pas membre du tableau",$tableau);
        }
        $participants = array_filter($tableau->getParticipants(), function ($u) use ($utilisateur) {
            return $u->getLogin() !== $utilisateur->getLogin();
        });
        $tableau->setParticipants($participants);
        $this->tableauRepository->mettreAJour($tableau);
        return $utilisateur;
    }

    /**
     * @throws ServiceException
     */
    public function recupererCompte($mail)
    {
        if (is_null($mail)) {
            throw new ServiceException("Adresse email manquante");
        }
        $utilisateurs = $this->utilisateurRepository->recupererUtilisateursParEmail($mail);
        if (empty($utilisateurs)) {
            throw new ServiceException("Aucun compte associé à cette adresse email");
        }
        return $utilisateurs;
    }

    /**
     * @throws TableauException
     */
    public function verificationsMembre(Tableau $tableau, $login)
    {
        $this->estProprietaire($tableau,$login);
        $utilisateurs = $this->utilisateurRepository->recupererUtilisateursOrderedPrenomNom();
        $filtredUtilisateurs = array_filter($utilisateurs, function ($u) use ($tableau) {
            return !$tableau->estParticipantOuProprietaire($u->getLogin());
        });

        if (empty($filtredUtilisateurs)) {
            //TODO le message flash est censé était en warning de base mais c'est maintenant un danger
            throw new TableauException("Il n'est pas possible d'ajouter plus de membre à ce tableau.",$tableau);
        }
        return $filtredUtilisateurs;
    }
}