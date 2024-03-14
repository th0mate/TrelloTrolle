<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Service\Exception\ConnexionException;
use App\Trellotrolle\Service\Exception\CreationCarteException;
use App\Trellotrolle\Service\Exception\MiseAJourCarteException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;
use App\Trellotrolle\Service\ServiceCarte;
use App\Trellotrolle\Service\ServiceColonne;
use App\Trellotrolle\Service\ServiceConnexion;
use App\Trellotrolle\Service\ServiceUtilisateur;

class ControleurCarte extends ControleurGenerique
{

    public static function afficherErreur($messageErreur = "", $controleur = ""): void
    {
        parent::afficherErreur($messageErreur, "carte");
    }

    public static function supprimerCarte(): void
    {
        $idCarte = $_REQUEST["idCarte"] ?? null;
        try {
            (new ServiceConnexion())->connecter();
            $carte = (new ServiceCarte())->recupererCarte($idCarte);
            $tableau = $carte->getColonne()->getTableau();
            (new ServiceUtilisateur())->estParticipant($tableau);
            $cartes = (new ServiceCarte())->supprimerCarte($tableau, $idCarte);
            if (count($cartes) > 0) {
                ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
            } else {
                ControleurCarte::redirection("tableau", "afficherListeMesTableaux");
            }
        } catch (ConnexionException $e) {
            self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            self::redirection("base", "accueil");
        }
    }

    public static function afficherFormulaireCreationCarte(): void
    {
        $idColonne = $_REQUEST['idColonne'] ?? null;
        try {
            (new ServiceConnexion())->connecter();
            $colonne = (new ServiceColonne())->recupererColonne($idColonne);
            $tableau = $colonne->getTableau();
            (new ServiceUtilisateur())->estParticipant($tableau);
            $colonnes = (new ServiceColonne())->recupererColonnesTableau($tableau->getIdTableau());
            ControleurTableau::afficherVue('vueGenerale.php', [
                "pagetitle" => "Création d'une carte",
                "cheminVueBody" => "carte/formulaireCreationCarte.php",
                "colonne" => $colonne,
                "colonnes" => $colonnes
            ]);
        } catch (ConnexionException $e) {
            self::redirectionConnectionFlash($e);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            self::redirection("base", "accueil");
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            self::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        }
    }

    public static function creerCarte(): void
    {
        $affectationsCarte = $_REQUEST["affectationsCarte"] ?? null;
        $idColonne = $_REQUEST["idColonne"] ?? null;
        $titreCarte=$_REQUEST['titreCarte'] ??null;
        $descCarte=$_REQUEST["descriptifCarte"] ?? null;
        $couleurCarte=$_REQUEST["couleurCarte"] ??null;
        try {
            (new ServiceConnexion())->connecter();
            $colonne = (new ServiceColonne())->recupererColonne($idColonne);
            (new ServiceCarte())->recupererAttributs([$titreCarte,$descCarte,$couleurCarte]);
            $tableau = $colonne->getTableau();
            (new ServiceUtilisateur())->estParticipant($tableau);
            $affectations=(new ServiceCarte())->creerCarte($tableau,$affectationsCarte);
            (new ServiceCarte())->newCarte($colonne,$titreCarte,$descCarte,$couleurCarte,$affectations);
            ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        } catch (ConnexionException $e) {
            self::redirectionConnectionFlash($e);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            self::redirection("base", 'accueil');
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            self::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (CreationCarteException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            ControleurCarte::redirection("carte", "afficherFormulaireCreationCarte", ["idColonne" => $_REQUEST["idColonne"]]);
        }
    }

    public static function afficherFormulaireMiseAJourCarte(): void
    {
        $idCarte = $_REQUEST['idCarte'] ?? null;
        try {
            (new ServiceConnexion())->connecter();
            $carte = (new ServiceCarte())->recupererCarte($idCarte);
            $tableau = $carte->getColonne()->getTableau();
            (new ServiceUtilisateur())->estParticipant($tableau);
            $colonnes = (new ServiceColonne())->recupererColonnesTableau($tableau->getIdTableau());
        } catch (ConnexionException $e) {
            self::redirectionConnectionFlash($e);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            self::redirection("base", "accueil");
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            self::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        }
        ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Modification d'une carte",
            "cheminVueBody" => "carte/formulaireMiseAJourCarte.php",
            "carte" => $carte,
            "colonnes" => $colonnes
        ]);
    }

    public static function mettreAJourCarte(): void
    {
        $idColonne = $_REQUEST["idColonne"] ?? null;
        $idCarte = $_REQUEST["idCarte"] ?? null;
        $attributs=[
            "titreCarte"=>$_REQUEST["titreCarte"]??null,
            "descriptifCarte"=>$_REQUEST["descriptifCarte"] ?? null,
            "couleurCarte"=>$_REQUEST["couleurCarte"] ??null,
            "affectationsCarte"=>$_REQUEST["affectationsCarte"] ??null,
        ];
        try {
            (new ServiceConnexion())->connecter();
            $colonne = (new ServiceColonne())->recupererColonne($idColonne);
            $carte = (new ServiceCarte())->recupererCarte($idCarte);
            (new ServiceCarte())->recupererAttributs($attributs);
            (new ServiceCarte())->verifs($carte,$colonne);
            $tableau=$colonne->getTableau();
            (new ServiceUtilisateur())->estParticipant($tableau);
            $attributs["affectationsCarte"]=(new ServiceCarte())->miseAJourCarte($tableau,$attributs["affectationsCarte"]);
            (new ServiceCarte())->carteUpdate($carte,$colonne,$attributs);
        } catch (ConnexionException $e) {
            self::redirectionConnectionFlash($e);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            self::redirection("base", "accueil");
        } catch (CreationCarteException $e) {
            MessageFlash::ajouter("danger",$e->getMessage());
            self::redirection("carte","afficherFormulaireMiseAJourCarte",['idCarte'=>$idCarte]);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger",$e->getMessage());
            self::redirection("tableau","afficherTableau",["codeTableau"=>$e->getTableau()->getCodeTableau()]);
        } catch (MiseAJourCarteException $e) {
            MessageFlash::ajouter("danger",$e->getMessage());
            self::redirection("carte",'afficherFormulaireCreationCarte',["idColonne"=>$colonne->getIdColonne()]);
        }
        ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    private static function redirectionConnectionFlash(ConnexionException $e): void
    {
        MessageFlash::ajouter("info", $e->getMessage());
        self::redirection("utilisateur", "afficherFormulaireConnexion");
    }
}