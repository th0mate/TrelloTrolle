<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Service\Exception\ConnexionException;
use App\Trellotrolle\Service\Exception\CreationException;
use App\Trellotrolle\Service\Exception\MiseAJourException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;
use App\Trellotrolle\Service\ServiceCarte;
use App\Trellotrolle\Service\ServiceColonne;
use App\Trellotrolle\Service\ServiceConnexion;
use App\Trellotrolle\Service\ServiceUtilisateur;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class ControleurCarte extends ControleurGenerique
{

    public static function afficherErreur($messageErreur = "", $controleur = ""): Response
    {
        return parent::afficherErreur($messageErreur, "carte");
    }

    #[Route('/carte/suprression', name: 'supprimerCarte',methods: "GET")]
    public static function supprimerCarte(): Response
    {
        $idCarte = $_REQUEST["idCarte"] ?? null;
        try {
            (new ServiceConnexion())->pasConnecter();
            $carte = (new ServiceCarte())->recupererCarte($idCarte);
            $tableau = $carte->getColonne()->getTableau();
            (new ServiceUtilisateur())->estParticipant($tableau);
            $cartes = (new ServiceCarte())->supprimerCarte($tableau, $idCarte);
            if (count($cartes) > 0) {
                return ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
            } else {
                return ControleurCarte::redirection("tableau", "afficherListeMesTableaux");
            }
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection("base", "accueil");
        }
    }

    #[Route('/carte/nouveau', name: 'afficherFormulaireCreationCarte',methods: "GET")]
    public static function afficherFormulaireCreationCarte(): Response
    {
        $idColonne = $_REQUEST['idColonne'] ?? null;
        try {
            (new ServiceConnexion())->pasConnecter();
            $colonne = (new ServiceColonne())->recupererColonne($idColonne);
            $tableau = $colonne->getTableau();
            (new ServiceUtilisateur())->estParticipant($tableau);
            $colonnes = (new ServiceColonne())->recupererColonnesTableau($tableau->getIdTableau());
            return ControleurTableau::afficherVue('vueGenerale.php', [
                "pagetitle" => "Création d'une carte",
                "cheminVueBody" => "carte/formulaireCreationCarte.php",
                "colonne" => $colonne,
                "colonnes" => $colonnes
            ]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection("base", "accueil");
        }
    }

    #[Route('/carte/nouveau', name: 'creerCarte',methods: "POST")]
    public static function creerCarte(): Response
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
            return ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (CreationException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurCarte::redirection("carte", "afficherFormulaireCreationCarte", ["idColonne" => $_REQUEST["idColonne"]]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection("base", 'accueil');
        }
    }

    #[Route('/carte/mettreAJour', name: 'afficherFormulaireMiseAJourCarte',methods: "GET")]
    public static function afficherFormulaireMiseAJourCarte(): Response
    {
        $idCarte = $_REQUEST['idCarte'] ?? null;
        try {
            (new ServiceConnexion())->pasConnecter();
            $carte = (new ServiceCarte())->recupererCarte($idCarte);
            $tableau = $carte->getColonne()->getTableau();
            (new ServiceUtilisateur())->estParticipant($tableau);
            $colonnes = (new ServiceColonne())->recupererColonnesTableau($tableau->getIdTableau());
            return ControleurTableau::afficherVue('vueGenerale.php', [
                "pagetitle" => "Modification d'une carte",
                "cheminVueBody" => "carte/formulaireMiseAJourCarte.php",
                "carte" => $carte,
                "colonnes" => $colonnes
            ]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection("base", "accueil");
        }
    }

    #[Route('/carte/mettreAJour', name: 'mettreAJourCarte',methods: "POST")]
    public static function mettreAJourCarte(): Response
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
            return ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (CreationException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("carte", "afficherFormulaireMiseAJourCarte", ['idCarte' => $idCarte]);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (MiseAJourException $e) {
            MessageFlash::ajouter($e->getTypeMessageFlash(), $e->getMessage());
            return self::redirection("carte", 'afficherFormulaireCreationCarte', ["idColonne" => $colonne->getIdColonne()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection("base", "accueil");
        }
    }

}