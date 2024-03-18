<?php

namespace App\Trellotrolle\Controleur;

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
use App\Trellotrolle\Service\Exception\ConnexionException;
use App\Trellotrolle\Service\Exception\CreationException;
use App\Trellotrolle\Service\Exception\MiseAJourException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\ServiceConnexion;
use App\Trellotrolle\Service\ServiceUtilisateur;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ControleurUtilisateur extends ControleurGenerique
{
    public static function afficherErreur($messageErreur = "", $controleur = ""): Response
    {
        return parent::afficherErreur($messageErreur, "utilisateur");
    }

    #[Route('/afficherDetail/{login}', name: 'afficherDetail',methods: "GET")]
    public static function afficherDetail(): Response
    {
        try {
            (new ServiceConnexion())->pasConnecter();
            $utilisateur = (new ServiceUtilisateur())->recupererUtilisateurParCle(ConnexionUtilisateur::getLoginUtilisateurConnecte());
            return ControleurUtilisateur::afficherVue('vueGenerale.php', [
                "utilisateur" => $utilisateur,
                "pagetitle" => "Détail de l'utilisateur {$utilisateur->getLogin()}",
                "cheminVueBody" => "utilisateur/detail.php"
            ]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        }
    }

    #[Route('/inscription', name: 'afficherFormulaireCreation', methods: "GET")]
    public static function afficherFormulaireCreation(): Response
    {
        try {
            (new ServiceConnexion())->dejaConnecter();
            return ControleurUtilisateur::afficherVue('vueGenerale.php', [
                "pagetitle" => "Création d'un utilisateur",
                "cheminVueBody" => "utilisateur/formulaireCreation.php"
            ]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        }
    }

    #[Route('/inscription', name: 'creerDepuisFormulaire',methods: "POST")]
    public static function creerDepuisFormulaire(): Response
    {
        $attributs = [
            "login" => $_REQUEST["login"] ?? null,
            "nom" => $_REQUEST["nom"] ?? null,
            "prenom" => $_REQUEST["prenom"] ?? null,
            "email" => $_REQUEST["email"] ?? null,
            "mdp" => $_REQUEST["mdp"] ?? null,
            "mdp2" => $_REQUEST["mdp2"] ?? null,
        ];
        try {
            (new ServiceConnexion())->dejaConnecter();
            (new ServiceUtilisateur())->creerUtilisateur($attributs);
            MessageFlash::ajouter("success", "L'utilisateur a bien été créé !");
            return ControleurUtilisateur::redirection( "afficherFormulaireConnexion");
        } catch (ConnexionException $e) {
            return self::redirection( "afficherListeMesTableaux");
        } catch (CreationException $e) {
            MessageFlash::ajouter("danger",$e->getMessage());
            return self::redirection("afficherFormulaireCreation");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection( "afficherFormulaireCreation");
        }
    }

    #[Route('/{login}/miseAJour', name: 'afficherFormulaireMiseAJourUtilisateur',methods: "GET")]
    public static function afficherFormulaireMiseAJour(): Response
    {
        try {
            (new ServiceConnexion())->pasConnecter();
            $utilisateur = (new ServiceUtilisateur())->recupererUtilisateurParCle(ConnexionUtilisateur::getLoginUtilisateurConnecte());
            return ControleurUtilisateur::afficherVue('vueGenerale.php', [
                "pagetitle" => "Mise à jour du profil",
                "cheminVueBody" => "utilisateur/formulaireMiseAJour.php",
                "utilisateur" => $utilisateur,
            ]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        }
    }

    #[Route('/{login}/miseAJour', name: 'mettreAJour', methods: "POST")]
    public static function mettreAJour(): Response
    {
        $attributs=[
            "login"=>$_REQUEST["login"] ??null,
            "nom"=>$_REQUEST["nom"] ??null,
            "prenom"=>$_REQUEST["prenom"] ??null,
            "email"=>$_REQUEST["email"] ??null,
            "mdp"=>$_REQUEST["mdp"] ??null,
            "mdp2"=>$_REQUEST["mdp2"] ??null,
            "mdpAncien"=>$_REQUEST["mdpAncien"]??null
        ];
        try{
            (new ServiceConnexion())->pasConnecter();
            (new ServiceUtilisateur())->mettreAJourUtilisateur($attributs);
            MessageFlash::ajouter("success", "L'utilisateur a bien été modifié !");
            return self::redirection( "afficherListeMesTableaux");
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (MiseAJourException $e) {
            MessageFlash::ajouter($e->getTypeMessageFlash(),$e->getMessage());
            return self::redirection("afficherFormulaireMiseAJour");
        }
    }

    #[Route('/{login}/supprimer', name: 'supprimer',methods: "POST")]
    public static function supprimer(): Response
    {
        $login=$_REQUEST["login"] ??null;
        try{
            (new ServiceConnexion())->pasConnecter();
            (new ServiceUtilisateur())->supprimerUtilisateur($login);
            MessageFlash::ajouter("success", "Votre compte a bien été supprimé !");
            return self::redirection( "afficherFormulaireConnexion");
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning",$e->getMessage());
            return self::redirection("afficherDetail");
        }
    }

    #[Route('/connexion', name: 'afficherFormulaireConnexion', methods: "GET")]
    public static function afficherFormulaireConnexion(): Response
    {
        try {
            (new ServiceConnexion())->dejaConnecter();
          return  ControleurUtilisateur::afficherVue('vueGenerale.php', [
                "pagetitle" => "Formulaire de connexion",
                "cheminVueBody" => "utilisateur/formulaireConnexion.php"
            ]);
        } catch (ConnexionException $e) {
            MessageFlash::ajouter("info", $e->getMessage());
            return self::redirection( "afficherListeMesTableaux");
        }
    }

    #[Route('/connexion', name: 'connecter',methods: "POST")]
    public static function connecter(): Response
    {
        $login=$_REQUEST["login"] ??null;
        $mdp=$_REQUEST["mdp"] ??null;
        try{
            (new ServiceConnexion())->dejaConnecter();
            (new ServiceConnexion())->connecter($login,$mdp);
            MessageFlash::ajouter("success", "Connexion effectuée.");
          return  self::redirection( "afficherListeMesTableaux");
        } catch (ConnexionException $e) {
           return self::redirection("afficherListeMesTableaux");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning",$e->getMessage());
            return self::redirection( "afficherFormulaireConnexion");
        }
    }

    #[Route('/deconnexion', name: 'deconnecter',methods: "GET")]
    public static function deconnecter(): Response
    {
        try {
            (new ServiceConnexion())->deconnecter();
            MessageFlash::ajouter("success", "L'utilisateur a bien été déconnecté.");
            return self::redirection( "accueil");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection( "accueil");
        }
    }

    #[Route('/recuperation', name: 'utilisateurResetCompte',methods: "GET")]
    public static function afficherFormulaireRecuperationCompte(): Response
    {
        try {
            (new ServiceConnexion())->dejaConnecter();
            return ControleurUtilisateur::afficherVue('vueGenerale.php', [
                "pagetitle" => "Récupérer mon compte",
                "cheminVueBody" => "utilisateur/resetCompte.php"
            ]);
        } catch (ConnexionException $e) {
            MessageFlash::ajouter("info", $e->getMessage());
            return self::redirection( "afficherListeMesTableaux");
        }
    }

    #[Route('/recuperation', name: 'recupererCompte',methods: "POST")]
    public static function recupererCompte(): Response
    {
        $mail = $_REQUEST["email"] ?? null;
        try {
            (new ServiceConnexion())->dejaConnecter();
            $utilisateurs = (new ServiceUtilisateur())->recupererCompte($mail);
            return ControleurUtilisateur::afficherVue('vueGenerale.php', [
                "pagetitle" => "Récupérer mon compte",
                "cheminVueBody" => "utilisateur/resultatResetCompte.php",
                "utilisateurs" => $utilisateurs
            ]);
        } catch (ConnexionException $e) {
            MessageFlash::ajouter("info", $e->getMessage());
            return self::redirection( "afficherListeMesTableaux");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection( "afficherFormulaireConnexion");
        }
    }
}