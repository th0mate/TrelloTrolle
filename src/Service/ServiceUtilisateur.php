<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Controleur\ControleurCarte;
use App\Trellotrolle\Controleur\ControleurTableau;
use App\Trellotrolle\Controleur\ControleurUtilisateur;
use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Lib\MotDePasse;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\HTTP\Cookie;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Service\Exception\CreationException;
use App\Trellotrolle\Service\Exception\MiseAJourException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;

class ServiceUtilisateur
{
    private UtilisateurRepository $utilisateurRepository;

    private TableauRepository $tableauRepository;

    private CarteRepository $carteRepository;

    public function __construct()
    {
        $this->carteRepository = new CarteRepository();
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
        $this->estProprietaire($tableau, ConnexionUtilisateur::getLoginUtilisateurConnecte());
        $this->isNotNullLogin($login, $tableau, "supprimer");
        $utilisateur = $this->utilisateurExistant($login, $tableau);
        if ($tableau->estProprietaire($login)) {
            throw new TableauException("Vous ne pouvez pas vous supprimer du tableau.", $tableau);
        }
        if (!$tableau->estParticipant($utilisateur->getLogin())) {
            throw new TableauException("Cet utilisateur n'est pas membre du tableau", $tableau);
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
        $this->estProprietaire($tableau, $login);
        $utilisateurs = $this->utilisateurRepository->recupererUtilisateursOrderedPrenomNom();
        $filtredUtilisateurs = array_filter($utilisateurs, function ($u) use ($tableau) {
            return !$tableau->estParticipantOuProprietaire($u->getLogin());
        });

        if (empty($filtredUtilisateurs)) {
            //TODO le message flash est censé était en warning de base mais c'est maintenant un danger
            throw new TableauException("Il n'est pas possible d'ajouter plus de membre à ce tableau.", $tableau);
        }
        return $filtredUtilisateurs;
    }

    /**
     * @throws MiseAJourException
     */
    public function mettreAJourUtilisateur($attributs)
    {
        foreach ($attributs as $attribut) {
            if (is_null($attribut)) {
                throw new MiseAJourException('Login, nom, prenom, email ou mot de passe manquant.', "danger");
            }
        }
        $login = $attributs['login'];

        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($login);

        if (!$utilisateur) {
            throw new MiseAJourException("L'utilisateur n'existe pas", "danger");
        }

        if (!filter_var($attributs["email"], FILTER_VALIDATE_EMAIL)) {
            throw new MiseAJourException("Email non valide", "warning");
        }

        if (!(MotDePasse::verifier($attributs["mdpAncien"], $utilisateur->getMdpHache()))) {
            throw new MiseAJourException("Ancien mot de passe erroné.", "warning");
        }

        if ($attributs["mdp"] !== $attributs["mdp2"]) {
            throw new MiseAJourException("Mots de passe distincts", "warning");
        }

        $utilisateur->setNom($attributs["nom"]);
        $utilisateur->setPrenom($attributs["prenom"]);
        $utilisateur->setEmail($attributs["email"]);
        $utilisateur->setMdpHache(MotDePasse::hacher($attributs["mdp"]));
        $utilisateur->setMdp($attributs["mdp"]);

        $this->utilisateurRepository->mettreAJour($utilisateur);

        $cartes = $this->carteRepository->recupererCartesUtilisateur($login);
        foreach ($cartes as $carte) {
            $participants = $carte->getAffectationsCarte();
            $participants = array_filter($participants, function ($u) use ($login) {
                return $u->getLogin() !== $login;
            });
            $participants[] = $utilisateur;
            $carte->setAffectationsCarte($participants);
            $this->carteRepository->mettreAJour($carte);
        }

        $tableaux = $this->tableauRepository->recupererTableauxParticipeUtilisateur($login);
        foreach ($tableaux as $tableau) {
            $participants = $tableau->getParticipants();
            $participants = array_filter($participants, function ($u) use ($login) {
                return $u->getLogin() !== $login;
            });
            $participants[] = $utilisateur;
            $tableau->setParticipants($participants);
            $this->tableauRepository->mettreAJour($tableau);
        }

        Cookie::enregistrer("mdp", $attributs["mdp"]);
    }

    /**
     * @throws ServiceException
     */
    public function supprimerUtilisateur($login)
    {
        if (is_null("login")) {
            throw new ServiceException("Login manquant");
        }
        $cartes = $this->carteRepository->recupererCartesUtilisateur($login);
        foreach ($cartes as $carte) {
            $participants = $carte->getAffectationsCarte();
            $participants = array_filter($participants, function ($u) use ($login) {
                return $u->getLogin() !== $login;
            });
            $carte->setAffectationsCarte($participants);
            $this->carteRepository->mettreAJour($carte);
        }

        $tableaux = $this->tableauRepository->recupererTableauxParticipeUtilisateur($login);
        foreach ($tableaux as $tableau) {
            $participants = $tableau->getParticipants();
            $participants = array_filter($participants, function ($u) use ($login) {
                return $u->getLogin() !== $login;
            });
            $tableau->setParticipants($participants);
            $this->tableauRepository->mettreAJour($tableau);
        }
        $this->utilisateurRepository->supprimer($login);
        Cookie::supprimer("login");
        Cookie::supprimer("mdp");
        ConnexionUtilisateur::deconnecter();
    }

    /**
     * @throws ServiceException
     * @throws \Exception
     */
    public function creerUtilisateur($attributs)
    {
        foreach ($attributs as $attribut) {
            if (is_null($attribut)) {
                throw new CreationException("Login, nom, prenom, email ou mot de passe manquant.");
            }
        }
        if ($attributs["mdp"] !== $attributs["mdp2"]) {
            throw new ServiceException("Mots de passe distincts");
        }

        if (!filter_var($attributs["email"], FILTER_VALIDATE_EMAIL)) {
            throw new ServiceException("Email non valide");
        }


        $checkUtilisateur = $this->utilisateurRepository->recupererParClePrimaire($attributs["login"]);
        if ($checkUtilisateur) {
            throw new ServiceException("Le login est déjà pris");
        }

        $mdpHache = MotDePasse::hacher($attributs["mdp"]);

        $utilisateur = new Utilisateur(
            $attributs["login"],
            $attributs["nom"],
            $attributs["prenom"],
            $attributs["email"],
            $mdpHache,
            $attributs["mdp"],
        );
        $succesSauvegarde=$this->utilisateurRepository->ajouter($utilisateur);
        if ($succesSauvegarde) {
            Cookie::enregistrer("login", $attributs["login"]);
            Cookie::enregistrer("mdp", $attributs["mdp"]);
        } else {
            throw new ServiceException("Une erreur est survenue lors de la création de l'utilisateur.");
        }
    }
}