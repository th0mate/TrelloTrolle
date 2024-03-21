<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Service\Exception\ConnexionException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauEception;
use App\Trellotrolle\Service\ServiceCarte;
use App\Trellotrolle\Service\ServiceConnexion;
use App\Trellotrolle\Service\ServiceTableau;
use App\Trellotrolle\Service\ServiceUtilisateur;

class ControleurTableau extends ControleurGenerique
{
    public static function afficherErreur($messageErreur = "", $controleur = ""): void
    {
        parent::afficherErreur($messageErreur, "tableau");
    }

    public static function afficherTableau(): void
    {
        $codeTableau = $_REQUEST["codeTableau"] ?? null;
        try {
            $tableau = (new ServiceTableau())->recupererTableauParCode($codeTableau);
            $donnes = (new ServiceTableau())->recupererCartesColonnes($tableau);
            ControleurTableau::afficherVue('vueGenerale.php', [
                "pagetitle" => "{$tableau->getTitreTableau()}",
                "cheminVueBody" => "tableau/tableau.php",
                "tableau" => $tableau,
                "colonnes" => $donnes["colonnes"],
                "participants" => $donnes["participants"],
                "data" => $donnes["data"],
            ]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            self::redirection("base", 'accueil');
        }


    }

    public static function afficherFormulaireMiseAJourTableau(): void
    {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        try {
            (new ServiceConnexion())->pasConnecter();
            $tableau = (new ServiceTableau())->recupererTableauParId($idTableau);
            (new ServiceUtilisateur())->estParticipant($tableau);
            ControleurTableau::afficherVue('vueGenerale.php', [
                "pagetitle" => "Modification d'un tableau",
                "cheminVueBody" => "tableau/formulaireMiseAJourTableau.php",
                "idTableau" => $_REQUEST["idTableau"],
                "nomTableau" => $tableau->getTitreTableau()
            ]);
        } catch (ConnexionException $e) {
            self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            self::redirection("base", "accueil");
        }
    }

    public static function afficherFormulaireCreationTableau(): void
    {
        try {
            (new ServiceConnexion())->pasConnecter();
            ControleurTableau::afficherVue('vueGenerale.php', [
                "pagetitle" => "Ajout d'un tableau",
                "cheminVueBody" => "tableau/formulaireCreationTableau.php",
            ]);
        } catch (ConnexionException $e) {
            self::redirectionConnectionFlash($e);
        }
    }

    public static function creerTableau(): void
    {
        $nomTableau=$_REQUEST["nomTableau"] ??null;
        try{
            (new ServiceConnexion())->pasConnecter();
            $tableau=(new ServiceTableau())->creerTableau($nomTableau);
            ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);

        } catch (ConnexionException $e) {
            self::redirectionConnectionFlash($e);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger",$e->getMessage());
            self::redirection("tableau","afficherFormulaireCreationTableau");
        }
    }

    public static function mettreAJourTableau(): void
    {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        $nomTableau = $_REQUEST["nomTableau"] ?? null;
        try {
            (new ServiceConnexion())->pasConnecter();
            $tableau = (new ServiceTableau())->recupererTableauParId($idTableau);
            (new ServiceTableau())->isNotNullNomTableau($nomTableau,$tableau);
            $estProprio=(new ServiceTableau())->estParticipant($tableau);
            if (!$estProprio) {
                MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            } else {
                $tableau->setTitreTableau($_REQUEST["nomTableau"]);
                (new ServiceTableau())->mettreAJourTableau($tableau);
            }
            ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        } catch (ConnexionException $e) {
            self::redirectionConnectionFlash($e);
        }catch (TableauException $e) {
            MessageFlash::ajouter("danger",$e->getMessage());
            ControleurTableau::redirection("tableau", "afficherFormulaireMiseAJourTableau", ["idTableau" => $_REQUEST["idTableau"]]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            self::redirection("base", "accueil");
        }
    }

    public static function afficherFormulaireAjoutMembre(): void
    {
        $idTableau=$_REQUEST["idTableau"] ??null;
        try{
            (new ServiceConnexion())->pasConnecter();
            $tableau=(new ServiceTableau())->recupererTableauParId($idTableau);
            $filtredUtilisateurs=(new ServiceUtilisateur())->verificationsMembre($tableau,ConnexionUtilisateur::getLoginUtilisateurConnecte());
            ControleurTableau::afficherVue('vueGenerale.php', [
                "pagetitle" => "Ajout d'un membre",
                "cheminVueBody" => "tableau/formulaireAjoutMembreTableau.php",
                "tableau" => $tableau,
                "utilisateurs" => $filtredUtilisateurs
            ]);
        } catch (ConnexionException $e) {
            self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger",$e->getMessage());
            ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        }catch (ServiceException $e) {
            MessageFlash::ajouter("danger",$e->getMessage());
            self::redirection("base","accueil");
        }
    }

    public static function ajouterMembre(): void
    {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        $login = $_REQUEST["login"] ?? null;
        try {
            (new ServiceConnexion())->pasConnecter();
            $tableau = (new ServiceTableau())->recupererTableauParId($idTableau);
            (new ServiceUtilisateur())->ajouterMembre($tableau, $login);
            ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        } catch (ConnexionException $e) {
            self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            self::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            self::redirection("base", "accueil");
        }
    }

    public static function supprimerMembre(): void
    {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        $login = $_REQUEST["login"] ?? null;
        try {
            (new ServiceConnexion())->pasConnecter();
            $tableau = (new ServiceTableau())->recupererTableauParId($idTableau);
            $utilisateur = (new ServiceUtilisateur())->supprimerMembre($tableau, $login);
            (new ServiceCarte())->miseAJourCarteMembre($tableau, $utilisateur);
            ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);

        } catch (ConnexionException $e) {
            self::redirectionConnectionFlash($e);
        }  catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            self::redirection("tableau", "afficherTableau", ['codeTableau' => $e->getTableau()->getCodeTableau()]);
        }catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            self::redirection("base", "accueil");
        }
    }

    public static function afficherListeMesTableaux(): void
    {
        try {
            (new ServiceConnexion())->pasConnecter();
            $login = ConnexionUtilisateur::getLoginUtilisateurConnecte();
            $tableaux = (new ServiceTableau())->recupererTableauEstMembre($login);
            ControleurTableau::afficherVue('vueGenerale.php', [
                "pagetitle" => "Liste des tableaux de $login",
                "cheminVueBody" => "tableau/listeTableauxUtilisateur.php",
                "tableaux" => $tableaux
            ]);
        } catch (ConnexionException $e) {
            self::redirectionConnectionFlash($e);
        }
    }

    public static function quitterTableau(): void
    {
        $idTableau=$_REQUEST["idTableau"] ??null;
        try{
            (new ServiceConnexion())->pasConnecter();
            $tableau=(new ServiceTableau())->recupererTableauParId($idTableau);
            $utilisateur=(new ServiceUtilisateur())->recupererUtilisateurParCle(ConnexionUtilisateur::getLoginUtilisateurConnecte());
            (new ServiceTableau())->quitterTableau($tableau,$utilisateur);
            ControleurTableau::redirection("tableau", "afficherListeMesTableaux");

        } catch (ConnexionException $e) {
            self::redirectionConnectionFlash($e);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger",$e->getMessage());
            self::redirection("tableau","afficherListeMesTableaux");
        }
    }

    public static function supprimerTableau(): void
    {
        $idTableau=$_REQUEST["idTableau"] ??null;
        try{
            (new ServiceConnexion())->pasConnecter();
            $tableau=(new ServiceTableau())->recupererTableauParId($idTableau);
            (new ServiceUtilisateur())->estProprietaire($tableau,ConnexionUtilisateur::getLoginUtilisateurConnecte());
            (new ServiceTableau())->supprimerTableau($idTableau);
            ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
        } catch (ConnexionException $e) {
            self::redirectionConnectionFlash($e);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger",$e->getMessage());
            self::redirection("tableau","afficherListeMesTableaux");
        }
    }
}