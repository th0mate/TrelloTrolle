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
use \App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\HTTP\Cookie;
use App\Trellotrolle\Modele\Repository\CarteRepository;

use App\Trellotrolle\Modele\Repository\CarteRepositoryInterface;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Modele\Repository\TableauRepositoryInterface;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepositoryInterface;
use App\Trellotrolle\Service\Exception\CreationException;
use App\Trellotrolle\Service\Exception\MiseAJourException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;
use Symfony\Component\HttpFoundation\Response;

class ServiceUtilisateur implements ServiceUtilisateurInterface
{

    public function __construct(private UtilisateurRepositoryInterface $utilisateurRepository,
                                private TableauRepositoryInterface     $tableauRepository,
                                private CarteRepositoryInterface       $carteRepository)
    {
    }

    /**
     * @throws TableauException
     */
    public function estParticipant(Tableau $tableau,$loginConnecte): void
    {

        if (!$this->tableauRepository->estParticipantOuProprietaire($loginConnecte, $tableau)) {
            throw new TableauException("Vous n'avez pas de droits d'éditions sur ce tableau", $tableau,Response::HTTP_FORBIDDEN);
        }
    }

    public function recupererUtilisateurParCle($login):Utilisateur|null
    {
        return $this->utilisateurRepository->recupererParClePrimaire($login);
    }

    /**
     * @throws TableauException
     */
    public function estProprietaire(Tableau $tableau, $login): void
    {
        if (!$this->tableauRepository->estProprietaire($login, $tableau)) {
            throw new TableauException("Vous n'êtes pas propriétaire de ce tableau", $tableau,Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * @throws TableauException
     */
    public function isNotNullLogin($login, Tableau $tableau, $action): void
    {
        if (is_null($login)) {
            throw new TableauException("Login du membre à " . $action . " manquant", $tableau,404);
        }
    }

    /**
     * @throws TableauException
     */
    public function utilisateurExistant($login, Tableau $tableau): AbstractDataObject
    {
        $utilisateur = $this->recupererUtilisateurParCle($login);
        if (!$utilisateur) {
            throw new TableauException("Utilisateur inexistant", $tableau,404);
        }
        return $utilisateur;
    }

    /**
     * @throws TableauException
     */
    public function ajouterMembre(Tableau $tableau, mixed $membresAAjouter,$loginConnecte): void
    {
        $this->estProprietaire($tableau, $loginConnecte);
        $this->isNotNullLogin($membresAAjouter, $tableau, "ajouter");
        $utilisateurs=[];
        foreach ($membresAAjouter as $user) {
            $utilisateur = $this->utilisateurExistant($user, $tableau);
            if ($this->tableauRepository->estParticipantOuProprietaire($utilisateur->getLogin(), $tableau)) {
                throw new TableauException("Ce membre est déjà membre du tableau", $tableau,Response::HTTP_CONFLICT);
            }
            $utilisateurs[]=$utilisateur;
        }
        $participants = $this->tableauRepository->getParticipants($tableau);
        $participants=array_merge($participants,$utilisateurs);
        $this->tableauRepository->setParticipants($participants, $tableau);
    }

    /**
     * @throws TableauException
     */
    public function supprimerMembre(Tableau $tableau, $login,$loginConnecte): AbstractDataObject
    {
        $this->estProprietaire($tableau,$loginConnecte);
        $this->isNotNullLogin($login, $tableau, "supprimer");
        $utilisateur = $this->utilisateurExistant($login, $tableau);
        if ($login==$loginConnecte) {
            throw new TableauException("Vous ne pouvez pas vous supprimer du tableau.", $tableau,403);
        }
        if (!$this->tableauRepository->estParticipant($utilisateur->getLogin(), $tableau)) {
            throw new TableauException("Cet utilisateur n'est pas membre du tableau", $tableau,Response::HTTP_FORBIDDEN);
        }
        $participants = array_filter($this->tableauRepository->getParticipants($tableau), function ($u) use ($utilisateur) {
            return $u->getLogin() !== $utilisateur->getLogin();
        });
        $this->tableauRepository->setParticipants($participants, $tableau);
        return $utilisateur;
    }

    /**
     * @throws ServiceException
     */
    public function recupererCompte($mail): array
    {
        if (is_null($mail)) {
            throw new ServiceException("Adresse email manquante",404);
        }
        $utilisateurs = $this->utilisateurRepository->recupererUtilisateursParEmail($mail);
        if (empty($utilisateurs)) {
            throw new ServiceException("Aucun compte associé à cette adresse email",404);
        }
        return $utilisateurs;
    }

    /**
     * @throws TableauException
     */
    public function verificationsMembre(Tableau $tableau, $login): array
    {
        $this->estProprietaire($tableau, $login);
        $utilisateurs = $this->utilisateurRepository->recupererUtilisateursOrderedPrenomNom();
        $filtredUtilisateurs = array_filter($utilisateurs, function ($u) use ($tableau) {
            return !$this->tableauRepository->estParticipantOuProprietaire($u->getLogin(), $tableau);
        });

        if (empty($filtredUtilisateurs)) {
            //TODO le message flash est censé était en warning de base mais c'est maintenant un danger
            throw new TableauException("Il n'est pas possible d'ajouter plus de membre à ce tableau.", $tableau,Response::HTTP_CONFLICT);
        }
        return $filtredUtilisateurs;
    }

    /**
     * @throws MiseAJourException
     */
    public function mettreAJourUtilisateur($attributs): void
    {
        foreach ($attributs as $attribut) {
            if (is_null($attribut)) {
                throw new MiseAJourException('Login, nom, prenom, email ou mot de passe manquant.', "danger",404);
            }
        }
        $login = $attributs['login'];
        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($login);

        if (!$utilisateur) {
            throw new MiseAJourException("L'utilisateur n'existe pas", "danger",404);
        }

        if (!filter_var($attributs["email"], FILTER_VALIDATE_EMAIL)) {
            throw new MiseAJourException("Email non valide", "warning",404);
        }

        if (!(MotDePasse::verifier($attributs["mdpAncien"], $utilisateur->getMdpHache()))) {
            throw new MiseAJourException("Ancien mot de passe erroné.", "warning",Response::HTTP_CONFLICT);
        }

        if ($attributs["mdp"] !== $attributs["mdp2"]) {
            throw new MiseAJourException("Mots de passe distincts", "warning",Response::HTTP_CONFLICT);
        }

        $utilisateur->setNom($attributs["nom"]);
        $utilisateur->setPrenom($attributs["prenom"]);
        $utilisateur->setEmail($attributs["email"]);
        $utilisateur->setMdpHache(MotDePasse::hacher($attributs["mdp"]));

        $this->utilisateurRepository->mettreAJour($utilisateur);
        /*
        $cartes = $this->carteRepository->recupererCartesUtilisateur($login);
        foreach ($cartes as $carte) {
            $participants = $this->carteRepository->getAffectationsCarte($carte);
            $participants = array_filter($participants, function ($u) use ($login) {
                return $u->getLogin() !== $login;
            });
            $participants[] = $utilisateur;
            $this->carteRepository->setAffectationsCarte($participants, $carte);
            $this->carteRepository->mettreAJour($carte);
        }

        $tableaux = $this->tableauRepository->recupererTableauxParticipeUtilisateur($login);
        foreach ($tableaux as $tableau) {
            $participants = $this->tableauRepository->getParticipants($tableau);
            $participants = array_filter($participants, function ($u) use ($login) {
                return $u->getLogin() !== $login;
            });
            $participants[] = $utilisateur;
            $this->tableauRepository->setParticipants($participants, $tableau);
            $this->tableauRepository->mettreAJour($tableau);
        }
        */


    }

    /**
     * @throws ServiceException
     */
    public function supprimerUtilisateur($login): void
    {
        if (is_null($login)) {
            throw new ServiceException("Login manquant",404);
        }
        $cartes = $this->carteRepository->recupererCartesUtilisateur($login);
        foreach ($cartes as $carte) {
            $participants = $this->carteRepository->getAffectationsCarte($carte);
            $participants = array_filter($participants, function ($u) use ($login) {
                return $u->getLogin() !== $login;
            });
            $this->carteRepository->setAffectationsCarte($participants, $carte);
        }

        $tableaux = $this->tableauRepository->recupererTableauxParticipeUtilisateur($login);
        foreach ($tableaux as $tableau) {
            $participants = $this->tableauRepository->getParticipants($tableau);
            $participants = array_filter($participants, function ($u) use ($login) {
                return $u->getLogin() !== $login;
            });
            $this->tableauRepository->setParticipants($participants, $tableau);
        }
        $this->utilisateurRepository->supprimer($login);
    }

    /**
     * @throws ServiceException
     * @throws \Exception
     */
    public function creerUtilisateur($attributs): void
    {
        foreach ($attributs as $attribut) {
            if (is_null($attribut)) {
                throw new CreationException("Login, nom, prenom, email ou mot de passe manquant.",404);
            }
        }
        if ($attributs["mdp"] !== $attributs["mdp2"]) {
            throw new ServiceException("Mots de passe distincts",Response::HTTP_CONFLICT);
        }

        if (!filter_var($attributs["email"], FILTER_VALIDATE_EMAIL)) {
            throw new ServiceException("Email non valide",404);
        }


        $checkUtilisateur = $this->utilisateurRepository->recupererParClePrimaire($attributs["login"]);
        if ($checkUtilisateur) {
            throw new ServiceException("Le login est déjà pris",Response::HTTP_FORBIDDEN);
        }

        $mdpHache = MotDePasse::hacher($attributs["mdp"]);

        $utilisateur = new Utilisateur(
            $attributs["login"],
            $attributs["nom"],
            $attributs["prenom"],
            $attributs["email"],
            $mdpHache,
        );
        $succesSauvegarde=$this->utilisateurRepository->ajouter($utilisateur);
        if (!$succesSauvegarde) {
            throw new ServiceException("Une erreur est survenue lors de la création de l'utilisateur.",Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @throws ServiceException
     */
    public function rechercheUtilisateur(?string $recherche): array
    {
        if (is_null($recherche)) {
            throw new ServiceException("La recherche est nulle", 404);
        }
        return $this->utilisateurRepository->recherche($recherche);
    }

    public function getParticipants(Tableau $tableau): ?array
    {
        return $this->tableauRepository->getParticipants($tableau);
    }

    //retourne le propriétaire du tableau
    public function getProprietaireTableau(Tableau $tableau): Utilisateur
    {
        return $this->tableauRepository->getProprietaire($tableau);
    }
}