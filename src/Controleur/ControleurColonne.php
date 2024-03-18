<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Service\Exception\ConnexionException;
use App\Trellotrolle\Service\Exception\CreationCarteException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;
use App\Trellotrolle\Service\ServiceCarte;
use App\Trellotrolle\Service\ServiceColonne;
use App\Trellotrolle\Service\ServiceConnexion;
use App\Trellotrolle\Service\ServiceTableau;
use App\Trellotrolle\Service\ServiceUtilisateur;
use Symfony\Component\Routing\Annotation\Route;

class ControleurColonne extends ControleurGenerique
{
    public static function afficherErreur($messageErreur = "", $controleur = ""): void
    {
        parent::afficherErreur($messageErreur, "colonne");
    }

    #[Route('/colonne/supprimerColonne', name: 'supprimerColonne')]
    public static function supprimerColonne(): void
    {
        $idColonne = $_REQUEST["idColonne"] ?? null;
        try {
            (new ServiceConnexion())->pasConnecter();
            $colonne = (new ServiceColonne())->recupererColonne($idColonne);
            $tableau = $colonne->getTableau();
            (new ServiceUtilisateur())->estParticipant($tableau);
            $nbColonnes = (new ServiceColonne())->supprimerColonne($tableau, $idColonne);
            if ($nbColonnes > 0) {
                ControleurColonne::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
            } else {
                ControleurCarte::redirection("tableau", "afficherListeMesTableaux");
            }
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

    #[Route('/colonne/afficherFormulaireCreationColonne', name: 'afficherFormulaireCreationColonne')]
    public static function afficherFormulaireCreationColonne(): void
    {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        try {
            (new ServiceConnexion())->pasConnecter();
            $tableau = (new ServiceTableau())->recupererTableauParId($idTableau);
            (new ServiceUtilisateur())->estParticipant($tableau);
        } catch (ConnexionException $e) {
            self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            self::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            self::redirection("base", "accueil");
        }
        ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Création d'une colonne",
            "cheminVueBody" => "colonne/formulaireCreationColonne.php",
            "idTableau" => $_REQUEST["idTableau"],
        ]);
    }

    #[Route('/colonne/creerColonne', name: 'creerColonne')]
    public static function creerColonne(): void
    {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        $nomColonne = $_REQUEST["nomColonne"] ?? null;
        try {
            (new ServiceConnexion())->pasConnecter();
            $tableau = (new ServiceTableau())->recupererTableauParId($idTableau);
            (new ServiceColonne())->isSetNomColonne($nomColonne);
            (new ServiceUtilisateur())->estParticipant($tableau);
            $colonne = (new ServiceColonne())->creerColonne($tableau, $nomColonne);
            //(new ServiceCarte())->newCarte($colonne,["Exemple","Exemple de carte","#FFFFFF",[]]);
            ControleurColonne::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        } catch (ConnexionException $e) {
            self::redirectionConnectionFlash($e);
        } catch (CreationCarteException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            ControleurColonne::redirection("colonne", "afficherFormulaireCreationColonne", ["idTableau" => $_REQUEST["idTableau"]]);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            self::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            self::redirection("base", "accueil");
        }
    }

    #[Route('/colonne/afficherFormulaireMiseAJourColonne', name: 'afficherFormulaireMiseAJourColonne')]
    public static function afficherFormulaireMiseAJourColonne(): void
    {
        $idColonne = $_REQUEST["idColonne"] ?? null;
        try {
            (new ServiceConnexion())->pasConnecter();
            $colonne = (new ServiceColonne())->recupererColonne($idColonne);
            $tableau = $colonne->getTableau();
            (new ServiceUtilisateur())->estParticipant($tableau);
            ControleurTableau::afficherVue('vueGenerale.php', [
                "pagetitle" => "Modification d'une colonne",
                "cheminVueBody" => "colonne/formulaireMiseAJourColonne.php",
                "idColonne" => $idColonne,
                "nomColonne" => $colonne->getTitreColonne()
            ]);
        } catch (ConnexionException $e) {
            self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            ControleurColonne::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            self::redirection("base", "accueil");
        }

    }

    #[Route('/colonne/mettreAJourColonne', name: 'mettreAJourColonne')]
    public static function mettreAJourColonne(): void
    {
        $idColonne = $_REQUEST["idColonne"] ?? null;
        $nomColonne = $_REQUEST["nomColonne"] ?? null;
        try {
            (new ServiceConnexion())->pasConnecter();
            $colonne = (new ServiceColonne())->recupererColonneAndNomColonne($idColonne, $nomColonne);
            $tableau = $colonne->getTableau();
            (new ServiceUtilisateur())->estParticipant($tableau);
            $colonne->setTitreColonne($nomColonne);
            (new ServiceColonne())->miseAJourColonne($colonne);
            ControleurColonne::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        } catch (ConnexionException $e) {
            self::redirectionConnectionFlash($e);
        } catch (CreationCarteException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            ControleurColonne::redirection("colonne", "afficherFormulaireMiseAJourColonne", ["idColonne" => $idColonne]);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            ControleurColonne::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            self::redirection("base", "accueil");
        }
    }
}