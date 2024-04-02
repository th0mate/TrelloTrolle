<?php

namespace App\Trellotrolle\Service;

use App\SAE\Model\Repository\EntrepriseRepository;
use App\Trellotrolle\Controleur\ControleurCarte;
use App\Trellotrolle\Controleur\ControleurTableau;
use App\Trellotrolle\Controleur\ControleurUtilisateur;
use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Lib\MotDePasse;
use App\Trellotrolle\Lib\VerificationEmail;
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

    /**
     * ServiceUtilisateur constructor.
     * @param UtilisateurRepositoryInterface $utilisateurRepository Repository des utilisateurs
     * @param TableauRepositoryInterface $tableauRepository Repository des tableaux
     * @param CarteRepositoryInterface $carteRepository Repository des cartes
     */
    public function __construct(private UtilisateurRepositoryInterface $utilisateurRepository,
                                private TableauRepositoryInterface     $tableauRepository,
                                private CarteRepositoryInterface       $carteRepository,
                                private VerificationEmail              $verificationEmail)
    {
    }


    /**
     * Fonction qui vérifie si l'utilisateur est participant d'un tableau
     * @param Tableau $tableau Le tableau sur lequel on veut vérifier si l'utilisateur est participant
     * @param $loginConnecte, Le login de l'utilisateur connecté
     * @return void
     * @throws TableauException Erreur si l'utilisateur n'est pas participant
     */
    public function estParticipant(Tableau $tableau, $loginConnecte): void
    {

        if (!$this->tableauRepository->estParticipantOuProprietaire($loginConnecte, $tableau)) {
            throw new TableauException("Vous n'avez pas de droits d'éditions sur ce tableau", $tableau, Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * Fonction qui récupère un utilisateur par sa clé
     * @param $login, La clé de l'utilisateur
     * @return AbstractDataObject|null L'utilisateur
     */
    public function recupererUtilisateurParCle($login):AbstractDataObject|null
    {
        return $this->utilisateurRepository->recupererParClePrimaire($login);
    }


    /**
     * Fonction qui vérifie si un utilisateur est le propriétaire d'un tableau
     * @param Tableau $tableau Le tableau sur lequel on veut vérifier si l'utilisateur est propriétaire
     * @param $login, La clé de l'utilisateur
     * @return void
     * @throws TableauException Erreur si l'utilisateur n'est pas propriétaire
     */
    public function estProprietaire(Tableau $tableau, $login): void
    {
        if (!$this->tableauRepository->estProprietaire($login, $tableau)) {
            throw new TableauException("Vous n'êtes pas propriétaire de ce tableau", $tableau, Response::HTTP_FORBIDDEN);
        }
    }


    /**
     * Fonction qui vérifie si un utilisateur n'a pas un login null
     * @param $login, Le login de l'utilisateur
     * @param Tableau $tableau Le tableau sur lequel on veut vérifier si l'utilisateur est présent
     * @param $action L'action à réaliser
     * @return void
     * @throws TableauException Erreur si le login est null
     */
    public function isNotNullLogin($login, Tableau $tableau, $action): void
    {
        if (is_null($login)) {
            throw new TableauException("Login du membre à " . $action . " manquant", $tableau, 404);
        }
    }


    /**
     * Fonction qui vérifie si un utilisateur existe
     * @param $login, Le login de l'utilisateur
     * @param Tableau $tableau Le tableau sur lequel on veut vérifier si l'utilisateur est présent
     * @return AbstractDataObject L'utilisateur
     * @throws TableauException Erreur si l'utilisateur n'existe pas
     */
    public function utilisateurExistant($login, Tableau $tableau): AbstractDataObject
    {
        $utilisateur = $this->recupererUtilisateurParCle($login);
        if (!$utilisateur) {
            throw new TableauException("Utilisateur inexistant", $tableau, 404);
        }
        return $utilisateur;
    }


    /**
     * Fonction qui ajoute un membre à un tableau
     * @param Tableau $tableau Le tableau sur lequel on veut ajouter un membre
     * @param mixed $login Le login de l'utilisateur à ajouter
     * @param $loginConnecte, Le login de l'utilisateur connecté
     * @return void
     * @throws TableauException Erreur si le membre est déjà membre du tableau
     */
    public function ajouterMembre(Tableau $tableau, mixed $membresAAjouter, $loginConnecte): void
    {
        $this->estProprietaire($tableau, $loginConnecte);
        $this->isNotNullLogin($membresAAjouter, $tableau, "ajouter");
        $utilisateurs = [];
        foreach ($membresAAjouter as $user) {
            $utilisateur = $this->utilisateurExistant($user, $tableau);
            if ($this->tableauRepository->estParticipantOuProprietaire($utilisateur->getLogin(), $tableau)) {
                throw new TableauException("Ce membre est déjà membre du tableau", $tableau, Response::HTTP_CONFLICT);
            }
            $utilisateurs[] = $utilisateur;
        }
        $participants = $this->tableauRepository->getParticipants($tableau);
        $participants = array_merge($participants, $utilisateurs);
        $this->tableauRepository->setParticipants($participants, $tableau);
    }


    /**
     * Fonction qui supprime un membre d'un tableau
     * @param Tableau $tableau Le tableau sur lequel on veut supprimer un membre
     * @param $login, Le login de l'utilisateur à supprimer
     * @param $loginConnecte, Le login de l'utilisateur connecté
     * @return AbstractDataObject L'utilisateur
     * @throws TableauException Erreur si l'utilisateur n'est pas membre du tableau
     * ou si l'utilisateur veut se supprimer lui-même
     */
    public function supprimerMembre(Tableau $tableau, $login, $loginConnecte): AbstractDataObject
    {
        $this->estProprietaire($tableau, $loginConnecte);
        $this->isNotNullLogin($login, $tableau, "supprimer");
        $utilisateur = $this->utilisateurExistant($login, $tableau);
        if ($login == $loginConnecte) {
            throw new TableauException("Vous ne pouvez pas vous supprimer du tableau.", $tableau, 403);
        }
        if (!$this->tableauRepository->estParticipant($utilisateur->getLogin(), $tableau)) {
            throw new TableauException("Cet utilisateur n'est pas membre du tableau", $tableau, Response::HTTP_FORBIDDEN);
        }
        $participants = array_filter($this->tableauRepository->getParticipants($tableau), function ($u) use ($utilisateur) {
            return $u->getLogin() !== $utilisateur->getLogin();
        });
        $this->tableauRepository->setParticipants($participants, $tableau);
        return $utilisateur;
    }


    /**
     * fonction qui récupère le compte d'un utilisateur par son mail
     * @param $mail, Le mail de l'utilisateur
     * @return array Le compte utilisateur
     * @throws ServiceException Erreur si l'adresse mail est manquante ou si aucun compte n'est associé à cette adresse mail
     */
    public function recupererCompte(?String $mail): void
    {
        if (is_null($mail)) {
            throw new ServiceException("Adresse email manquante", 404);
        }
        $utilisateurs = $this->utilisateurRepository->recupererUtilisateursParEmail($mail);
        if (empty($utilisateurs)) {
            throw new ServiceException("Aucun compte associé à cette adresse email", 404);
        }
        $utilisateurs->setNonce(MotDePasse::genererChaineAleatoire());
        $this->utilisateurRepository->mettreAJour($utilisateurs);
        $this->verificationEmail->envoiEmailChangementPassword($utilisateurs);

    }


    /**
     * Fonction qui vérifie si un utilisateur est un membre d'un tableau
     * @param Tableau $tableau Le tableau sur lequel on veut vérifier si l'utilisateur est membre
     * @param $login Le login de l'utilisateur
     * @return array Les membres du tableau
     * @throws TableauException Erreur si le nombre de participants maximum est déjà atteint
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
            throw new TableauException("Il n'est pas possible d'ajouter plus de membre à ce tableau.", $tableau, Response::HTTP_CONFLICT);
        }
        return $filtredUtilisateurs;
    }


    /**
     * Fonction qui met à jour un utilisateur avec les attributs passés en paramètre
     * @param $attributs, Les nouveaux attributs de l'utilisateur
     * @return void
     * @throws MiseAJourException Erreur si un des attributs est manquant,
     * si l'email n'est pas valide, si l'ancien mot de passe est erroné,
     * si l'utilisateur n'existe pas ou si les mots de passe sont distincts
     */
    public function mettreAJourUtilisateur($attributs): void
    {
        foreach ($attributs as $attribut) {
            if (is_null($attribut)) {
                throw new MiseAJourException('Login, nom, prenom, email ou mot de passe manquant.', "danger", 404);
            }
        }
        $login = $attributs['login'];
        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($login);

        if (!$utilisateur) {
            throw new MiseAJourException("L'utilisateur n'existe pas", "danger", 404);
        }

        if (!filter_var($attributs["email"], FILTER_VALIDATE_EMAIL)) {
            throw new MiseAJourException("Email non valide", "warning", 404);
        }
        $checkUtilisateur= $this->utilisateurRepository->recupererUtilisateursParEmail($attributs["email"]);
        if($checkUtilisateur){
            throw new MiseAJourException("L'email est déjà utilisé", "warning", 403);
        }

        if (!(MotDePasse::verifier($attributs["mdpAncien"], $utilisateur->getMdpHache()))) {
            throw new MiseAJourException("Ancien mot de passe erroné.", "warning", Response::HTTP_CONFLICT);
        }

        if ($attributs["mdp"] !== $attributs["mdp2"]) {
            throw new MiseAJourException("Mots de passe distincts", "warning", Response::HTTP_CONFLICT);
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
     * Fonction qui supprime un utilisateur
     * @param $login, Le login de l'utilisateur à supprimer
     * @return void
     * @throws ServiceException Erreur si le login est manquant
     */
    public function supprimerUtilisateur($login): void
    {
        if (is_null($login)) {
            throw new ServiceException("Login manquant", 404);
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
     * @param $attributs, Les attributs de l'utilisateur à créer
     * @return void
     * @throws CreationException Erreur si un des attributs est manquant
     * @throws ServiceException Erreur si le login est déjà pris,
     * si les mots de passe sont distincts
     * ou si l'email n'est pas valide.
     */
    public function creerUtilisateur($attributs): void
    {
        foreach ($attributs as $attribut) {
            if (is_null($attribut)) {
                throw new CreationException("Login, nom, prenom, email ou mot de passe manquant.", 404);
            }
        }
        if ($attributs["mdp"] !== $attributs["mdp2"]) {
            throw new ServiceException("Mots de passe distincts", Response::HTTP_CONFLICT);
        }

        if (!filter_var($attributs["email"], FILTER_VALIDATE_EMAIL)) {
            throw new ServiceException("Email non valide", 404);
        }


        $checkUtilisateur = $this->utilisateurRepository->recupererParClePrimaire($attributs["login"]);
        if ($checkUtilisateur) {
            throw new ServiceException("Le login est déjà pris", Response::HTTP_FORBIDDEN);
        }
        $checkUtilisateur= $this->utilisateurRepository->recupererUtilisateursParEmail($attributs["email"]);
        if ($checkUtilisateur) {
            throw new ServiceException("L'email est déjà utilisé", 409);
        }

        $mdpHache = MotDePasse::hacher($attributs["mdp"]);

        $utilisateur = new Utilisateur(
            $attributs["login"],
            $attributs["nom"],
            $attributs["prenom"],
            $attributs["email"],
            $mdpHache,
            ""
        );
        $succesSauvegarde = $this->utilisateurRepository->ajouter($utilisateur);
        if (!$succesSauvegarde) {
            throw new ServiceException("Une erreur est survenue lors de la création de l'utilisateur.", Response::HTTP_BAD_REQUEST);
        }
    }


    /**
     * Fonction qui recherche un utilisateur
     * @param string|null $recherche Le login de l'utilisateur à rechercher
     * @return array Les utilisateurs trouvés
     * @throws ServiceException Erreur si la recherche est nulle
     */
    public function rechercheUtilisateur(?string $recherche): array
    {
        if (is_null($recherche)) {
            throw new ServiceException("La recherche est nulle", 404);
        }
        return $this->utilisateurRepository->recherche($recherche);
    }

    /**
     * Fonction qui récupère les participants d'un tableau
     * @param Tableau $tableau Le tableau sur lequel on veut récupérer les participants
     * @return array|null Les participants du tableau
     */
    public function getParticipants(Tableau $tableau): ?array
    {
        return $this->tableauRepository->getParticipants($tableau);
    }

    //retourne le propriétaire du tableau

    /**
     * Fonction qui récupère le propriétaire d'un tableau
     * @param Tableau $tableau Le tableau sur lequel on veut récupérer le propriétaire
     * @return Utilisateur Le propriétaire du tableau
     */
    public function getProprietaireTableau(Tableau $tableau): Utilisateur
    {
        return $this->tableauRepository->getProprietaire($tableau);
    }


    /**
     * Fonction qui récupère les affectations d'un utilisateur
     * @param $colonne, La colonne sur laquelle on veut récupérer les affectations
     * @param $login, Le login de l'utilisateur
     * @return array Les affectations de l'utilisateur sur la colonne
     */
    public function recupererAffectationsColonne($colonne, $login)
    {
        $participants = [];
        $cartes = $this->carteRepository->recupererCartesColonne($colonne->getIdColonne());
        foreach ($cartes as $carte) {
            foreach ($this->carteRepository->getAffectationsCarte($carte) as $utilisateur) {
                if (!isset($participants[$utilisateur->getLogin()])) {
                    $participants[$utilisateur->getLogin()] = ["colonnes" => []];
                }
                if (!isset($participants[$utilisateur->getLogin()]["colonnes"][$colonne->getIdColonne()])) {
                    $participants[$utilisateur->getLogin()]["colonnes"][$colonne->getIdColonne()] = [$colonne->getTitreColonne(), 0];
                }
                $participants[$utilisateur->getLogin()]["colonnes"][$colonne->getIdColonne()][1]++;
            }
        }
        return $participants;
    }

    /**
     * @throws ServiceException
     */
    public function verifNonce($login, $nonce): void
    {
        if (is_null($login) || is_null($nonce)){
            throw new ServiceException("Informations manquantes",404);
        }
        $utilisateur=$this->recupererUtilisateurParCle($login);
        if (!$utilisateur) {
            throw new ServiceException("Utilisateur inexistante",404);
        }
        if ($utilisateur->formatTableau()["nonceTag"] != $nonce) {
            throw new ServiceException("Le nonce est incorrect", Response::HTTP_FORBIDDEN);
        }
    }

    public function changerMotDePasse($login, $mdp, $mdp2): void
    {
        if (is_null($login) ||is_null($mdp) || is_null($mdp2)) {
            throw new ServiceException("Informations manquantes", 404);
        }
        if ($mdp !== $mdp2) {
            throw new ServiceException("Mot de passe différent", Response::HTTP_CONFLICT);
        }
        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($login);
        if (is_null($utilisateur)) {
            throw new ServiceException("Utilisateur inexistant", Response::HTTP_NOT_FOUND);
        }
        $utilisateur->setMdpHache(MotDePasse::hacher($mdp));
        $utilisateur->setNonce("");
        $this->utilisateurRepository->mettreAJour($utilisateur);
    }
}