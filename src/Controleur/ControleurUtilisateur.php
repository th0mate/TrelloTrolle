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
use App\Trellotrolle\Service\Exception\MiseAJourException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\ServiceConnexion;
use App\Trellotrolle\Service\ServiceUtilisateur;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ControleurUtilisateur extends ControleurGenerique
{
    public static function afficherErreur($messageErreur = "", $controleur = ""): void
    {
        parent::afficherErreur($messageErreur, "utilisateur");
    }

    #[Route('/utilisateur/detail', name: 'afficherDetails')]
    public static function afficherDetail(): void
    {
        try {
            (new ServiceConnexion())->pasConnecter();
            $utilisateur = (new ServiceUtilisateur())->recupererUtilisateurParCle(ConnexionUtilisateur::getLoginUtilisateurConnecte());
            ControleurUtilisateur::afficherVue('vueGenerale.php', [
                "utilisateur" => $utilisateur,
                "pagetitle" => "Détail de l'utilisateur {$utilisateur->getLogin()}",
                "cheminVueBody" => "utilisateur/detail.php"
            ]);
        } catch (ConnexionException $e) {
            self::redirectionConnectionFlash($e);
        }
    }

    #[Route('/utilisateur/formulaireCreation', name: 'afficherFormulaireCreation')]
    public static function afficherFormulaireCreation(): void
    {
        try {
            (new ServiceConnexion())->dejaConnecter();
            ControleurUtilisateur::afficherVue('vueGenerale.php', [
                "pagetitle" => "Création d'un utilisateur",
                "cheminVueBody" => "utilisateur/formulaireCreation.php"
            ]);
        } catch (ConnexionException $e) {
            self::redirectionConnectionFlash($e);
        }
    }

    #[Route('/utilisateur/creerDepuisFormulaire', name: 'creerDepuisFormulaire')]
    public static function creerDepuisFormulaire(): Response
    {
        if (ConnexionUtilisateur::estConnecte()) {
           return ControleurTableau::redirection("utilisateur", "afficherListeMesTableaux");
        }
        if (ControleurUtilisateur::issetAndNotNull(["login", "prenom", "nom", "mdp", "mdp2", "email"])) {
            if ($_REQUEST["mdp"] !== $_REQUEST["mdp2"]) {
                MessageFlash::ajouter("warning", "Mots de passe distincts.");
            return    (new ControleurUtilisateur)->redirection("utilisateur", "afficherFormulaireCreation");
            }

            if (!filter_var($_REQUEST["email"], FILTER_VALIDATE_EMAIL)) {
                MessageFlash::ajouter("warning", "Email non valide");
             return   (new ControleurUtilisateur)->redirection("utilisateur", "afficherFormulaireCreation");
            }

            $utilisateurRepository = new UtilisateurRepository();

            $checkUtilisateur = $utilisateurRepository->recupererParClePrimaire($_REQUEST["login"]);
            if ($checkUtilisateur) {
                MessageFlash::ajouter("warning", "Le login est déjà pris.");
              return  (new ControleurUtilisateur)->redirection("utilisateur", "afficherFormulaireCreation");
            }

            $tableauRepository = new TableauRepository();
            $colonneRepository = new ColonneRepository();
            $carteRepository = new CarteRepository();

            $mdpHache = MotDePasse::hacher($_REQUEST["mdp"]);
            $idTableau = $tableauRepository->getNextIdTableau();
            $codeTableau = hash("sha256", $_REQUEST["login"] . $idTableau);
            $tableauInitial = "Mon tableau";

            $idColonne1 = $colonneRepository->getNextIdColonne();
            $colonne1 = "TODO";

            $colonne2 = "DOING";
            $idColonne2 = $idColonne1 + 1;

            $colonne3 = "DONE";
            $idColonne3 = $idColonne1 + 2;

            $carteInitiale = "Exemple";
            $descriptifInitial = "Exemple de carte";
            $idCarte1 = $carteRepository->getNextIdCarte();
            $idCarte2 = $idCarte1 + 1;
            $idCarte3 = $idCarte1 + 2;

            $tableau = new Tableau(
                new Utilisateur(
                    $_REQUEST["login"],
                    $_REQUEST["nom"],
                    $_REQUEST["prenom"],
                    $_REQUEST["email"],
                    $mdpHache,
                    $_REQUEST["mdp"],
                ),
                $idTableau,
                $codeTableau,
                $tableauInitial,
                [],
            );

            $carte1 = new Carte(
                new Colonne(
                    $tableau,
                    $idColonne1,
                    $colonne1,
                ),
                $idCarte1,
                $carteInitiale,
                $descriptifInitial,
                "#FFFFFF",
                []
            );

            $carte2 = new Carte(
                new Colonne(
                    $tableau,
                    $idColonne2,
                    $colonne2,
                ),
                $idCarte2,
                $carteInitiale,
                $descriptifInitial,
                "#FFFFFF",
                []
            );

            $carte3 = new Carte(
                new Colonne(
                    $tableau,
                    $idColonne3,
                    $colonne3,
                ),
                $idCarte3,
                $carteInitiale,
                $descriptifInitial,
                "#FFFFFF",
                []
            );

            $succesSauvegarde = $carteRepository->ajouter($carte1) && $carteRepository->ajouter($carte2) && $carteRepository->ajouter($carte3);
            if ($succesSauvegarde) {
                Cookie::enregistrer("login", $_REQUEST["login"]);
                Cookie::enregistrer("mdp", $_REQUEST["mdp"]);
                MessageFlash::ajouter("success", "L'utilisateur a bien été créé !");
              return  (new ControleurUtilisateur)->redirection("utilisateur", "afficherFormulaireConnexion");
            } else {
                MessageFlash::ajouter("warning", "Une erreur est survenue lors de la création de l'utilisateur.");
             return   (new ControleurUtilisateur)->redirection("utilisateur", "afficherFormulaireCreation");
            }
        } else {
            MessageFlash::ajouter("danger", "Login, nom, prenom, email ou mot de passe manquant.");
          return  (new ControleurUtilisateur)->redirection("utilisateur", "afficherFormulaireCreation");
        }
    }

    #[Route('/utilisateur/formulaireMiseAJour', name: 'afficherFormulaireMiseAJourUtilisateur')]
    public static function afficherFormulaireMiseAJour(): void
    {
        try {
            (new ServiceConnexion())->pasConnecter();
            $utilisateur = (new ServiceUtilisateur())->recupererUtilisateurParCle(ConnexionUtilisateur::getLoginUtilisateurConnecte());
            ControleurUtilisateur::afficherVue('vueGenerale.php', [
                "pagetitle" => "Mise à jour du profil",
                "cheminVueBody" => "utilisateur/formulaireMiseAJour.php",
                "utilisateur" => $utilisateur,
            ]);
        } catch (ConnexionException $e) {
            self::redirectionConnectionFlash($e);
        }
    }

    #[Route('/utilisateur/mettreAJour', name: 'mettreAJour')]
    public static function mettreAJour(): void
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
            (new ControleurUtilisateur)->redirection("tableau", "afficherListeMesTableaux");
        } catch (ConnexionException $e) {
            self::redirectionConnectionFlash($e);
        } catch (MiseAJourException $e) {
            MessageFlash::ajouter($e->getTypeMessageFlash(),$e->getMessage());
            self::redirection("utilisateur","afficherFormulaireMiseAJour");
        }
    }

    #[Route('/utilisateur/supprimer', name: 'supprimer')]
    public static function supprimer(): void
    {
        $login=$_REQUEST["login"] ??null;
        try{
            (new ServiceConnexion())->pasConnecter();
            (new ServiceUtilisateur())->supprimerUtilisateur($login);
            MessageFlash::ajouter("success", "Votre compte a bien été supprimé !");
            (new ControleurUtilisateur)->redirection("utilisateur", "afficherFormulaireConnexion");
        } catch (ConnexionException $e) {
            self::redirectionConnectionFlash($e);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning",$e->getMessage());
            self::redirection("utilisateur","afficherDetail");
        }
    }

    #[Route('/utilisateur/afficherFormulaireConnexion', name: 'afficherFormulaireConnexion')]
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
           return self::redirection("utilisateur", "afficherListeMesTableaux");
        }
    }

    #[Route('/utilisateur/connecter', name: 'connecter')]
    public static function connecter(): Response
    {
        $login=$_REQUEST["login"] ??null;
        $mdp=$_REQUEST["mdp"] ??null;
        try{
            (new ServiceConnexion())->dejaConnecter();
            (new ServiceConnexion())->connecter($login,$mdp);
            MessageFlash::ajouter("success", "Connexion effectuée.");
          return  (new ControleurUtilisateur)->redirection("tableau", "afficherListeMesTableaux");
        } catch (ConnexionException $e) {
           return (new ControleurUtilisateur)->redirection("utilisateur","afficherListeMesTableaux");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning",$e->getMessage());
            return (new ControleurUtilisateur)->redirection("utilisateur", "afficherFormulaireConnexion");
        }
    }

    #[Route('/utilisateur/deconnecter', name: 'deconnexion')]
    public static function deconnecter(): void
    {
        try {
            (new ServiceConnexion())->deconnecter();
            MessageFlash::ajouter("success", "L'utilisateur a bien été déconnecté.");
            (new ControleurUtilisateur)->redirection("base", "accueil");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            self::redirection("base", "accueil");
        }
    }

    #[Route('/utilisateur/resetCompte', name: 'utilisateurResetCompte')]
    public static function afficherFormulaireRecuperationCompte(): void
    {
        try {
            (new ServiceConnexion())->dejaConnecter();
            ControleurUtilisateur::afficherVue('vueGenerale.php', [
                "pagetitle" => "Récupérer mon compte",
                "cheminVueBody" => "utilisateur/resetCompte.php"
            ]);
        } catch (ConnexionException $e) {
            MessageFlash::ajouter("info", $e->getMessage());
            self::redirection("utilisateur", "afficherListeMesTableaux");
        }
    }

    #[Route('/utilisateur/recupererCompte', name: 'recupererCompte')]
    public static function recupererCompte(): void
    {
        $mail = $_REQUEST["email"] ?? null;
        try {
            (new ServiceConnexion())->dejaConnecter();
            $utilisateurs = (new ServiceUtilisateur())->recupererCompte($mail);
            ControleurUtilisateur::afficherVue('vueGenerale.php', [
                "pagetitle" => "Récupérer mon compte",
                "cheminVueBody" => "utilisateur/resultatResetCompte.php",
                "utilisateurs" => $utilisateurs
            ]);
        } catch (ConnexionException $e) {
            MessageFlash::ajouter("info", $e->getMessage());
            self::redirection("utilisateur", "afficherListeMesTableaux");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            self::redirection("utilisateur", "afficherFormulaireConnexion");
        }
    }
}