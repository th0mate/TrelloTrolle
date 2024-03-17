<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Service\Exception\ConnexionException;
use App\Trellotrolle\Service\Exception\CreationCarteException;
use App\Trellotrolle\Service\Exception\MiseAJourException;
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
            (new ServiceConnexion())->pasConnecter();
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
            (new ServiceConnexion())->pasConnecter();
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
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            self::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            self::redirection("base", "accueil");
        }
    }

    public static function creerCarte(): void
    {
        $idColonne = $_REQUEST["idColonne"] ?? null;
        $attributs = [
            "titreCarte" => $_REQUEST["titreCarte"] ?? null,
            "descriptifCarte" => $_REQUEST["descriptifCarte"] ?? null,
            "couleurCarte" => $_REQUEST["couleurCarte"] ?? null,
            "affectationsCarte" => $_REQUEST["affectationsCarte"] ?? null,
        ];
        try {
            (new ServiceConnexion())->pasConnecter();
            $colonne = (new ServiceColonne())->recupererColonne($idColonne);
            (new ServiceCarte())->recupererAttributs($attributs);
            $tableau = $colonne->getTableau();
            (new ServiceUtilisateur())->estParticipant($tableau);
            (new ServiceCarte())->creerCarte($tableau, $attributs, $colonne);
            ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        } catch (ConnexionException $e) {
            self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            self::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (CreationCarteException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            ControleurCarte::redirection("carte", "afficherFormulaireCreationCarte", ["idColonne" => $_REQUEST["idColonne"]]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            self::redirection("base", 'accueil');
        }
    }

    public static function afficherFormulaireMiseAJourCarte(): void
    {
        $idCarte = $_REQUEST['idCarte'] ?? null;
        try {
            (new ServiceConnexion())->pasConnecter();
            $carte = (new ServiceCarte())->recupererCarte($idCarte);
            $tableau = $carte->getColonne()->getTableau();
            (new ServiceUtilisateur())->estParticipant($tableau);
            $colonnes = (new ServiceColonne())->recupererColonnesTableau($tableau->getIdTableau());
            ControleurTableau::afficherVue('vueGenerale.php', [
                "pagetitle" => "Modification d'une carte",
                "cheminVueBody" => "carte/formulaireMiseAJourCarte.php",
                "carte" => $carte,
                "colonnes" => $colonnes
            ]);
        } catch (ConnexionException $e) {
            self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            self::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            self::redirection("base", "accueil");
        }
    }

    public static function mettreAJourCarte(): void
    {
        $idColonne = $_REQUEST["idColonne"] ?? null;
        $idCarte = $_REQUEST["idCarte"] ?? null;
        $attributs = [
            "titreCarte" => $_REQUEST["titreCarte"] ?? null,
            "descriptifCarte" => $_REQUEST["descriptifCarte"] ?? null,
            "couleurCarte" => $_REQUEST["couleurCarte"] ?? null,
            "affectationsCarte" => $_REQUEST["affectationsCarte"] ?? null,
        ];
        try {
            (new ServiceConnexion())->pasConnecter();
            $colonne = (new ServiceColonne())->recupererColonne($idColonne);
            $carte = (new ServiceCarte())->verificationsMiseAJourCarte($idCarte, $colonne, $attributs);
            $tableau = $colonne->getTableau();
            (new ServiceUtilisateur())->estParticipant($tableau);
            (new ServiceCarte())->miseAJourCarte($tableau, $attributs, $carte, $colonne);
            ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        } catch (ConnexionException $e) {
            self::redirectionConnectionFlash($e);
        } catch (CreationCarteException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            self::redirection("carte", "afficherFormulaireMiseAJourCarte", ['idCarte' => $idCarte]);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            self::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (MiseAJourException $e) {
            MessageFlash::ajouter($e->getTypeMessageFlash(), $e->getMessage());
            self::redirection("carte", 'afficherFormulaireCreationCarte', ["idColonne" => $colonne->getIdColonne()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            self::redirection("base", "accueil");
        }
    }

}