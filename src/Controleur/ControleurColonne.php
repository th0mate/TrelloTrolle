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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ControleurColonne extends ControleurGenerique
{
    public static function afficherErreur($messageErreur = "", $controleur = ""): \Symfony\Component\HttpFoundation\Response
    {
        return parent::afficherErreur($messageErreur, "colonne");
    }

    #[Route('/colonne/suppression', name: 'supprimerColonne',methods: "GET")]
    public static function supprimerColonne(): Response
    {
        $idColonne = $_REQUEST["idColonne"] ?? null;
        try {
            (new ServiceConnexion())->pasConnecter();
            $colonne = (new ServiceColonne())->recupererColonne($idColonne);
            $tableau = $colonne->getTableau();
            (new ServiceUtilisateur())->estParticipant($tableau);
            $nbColonnes = (new ServiceColonne())->supprimerColonne($tableau, $idColonne);
            if ($nbColonnes > 0) {
                return ControleurColonne::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
            } else {
                return ControleurCarte::redirection("tableau", "afficherListeMesTableaux");
            }
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("base", "accueil");
        }
    }

    #[Route('/colonne/nouveau', name: 'afficherFormulaireCreationColonne',methods: "GET")]
    public static function afficherFormulaireCreationColonne(): Response
    {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        try {
            (new ServiceConnexion())->pasConnecter();
            $tableau = (new ServiceTableau())->recupererTableauParId($idTableau);
            (new ServiceUtilisateur())->estParticipant($tableau);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection("base", "accueil");
        }
        return ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Création d'une colonne",
            "cheminVueBody" => "colonne/formulaireCreationColonne.php",
            "idTableau" => $_REQUEST["idTableau"],
        ]);
    }

    #[Route('/colonne/nouveau', name: 'creerColonne',methods: "POST")]
    public static function creerColonne(): Response
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
            return ControleurColonne::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (CreationCarteException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurColonne::redirection("colonne", "afficherFormulaireCreationColonne", ["idTableau" => $_REQUEST["idTableau"]]);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("base", "accueil");
        }
    }

    #[Route('/colonne/mettreAJour', name: 'afficherFormulaireMiseAJourColonne',methods: "GET")]
    public static function afficherFormulaireMiseAJourColonne(): Response
    {
        $idColonne = $_REQUEST["idColonne"] ?? null;
        try {
            (new ServiceConnexion())->pasConnecter();
            $colonne = (new ServiceColonne())->recupererColonne($idColonne);
            $tableau = $colonne->getTableau();
            (new ServiceUtilisateur())->estParticipant($tableau);
            return ControleurTableau::afficherVue('vueGenerale.php', [
                "pagetitle" => "Modification d'une colonne",
                "cheminVueBody" => "colonne/formulaireMiseAJourColonne.php",
                "idColonne" => $idColonne,
                "nomColonne" => $colonne->getTitreColonne()
            ]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurColonne::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("base", "accueil");
        }

    }

    #[Route('/colonne/mettreAJour', name: 'mettreAJourColonne',methods: "POST")]
    public static function mettreAJourColonne(): Response
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
            return ControleurColonne::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (CreationCarteException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurColonne::redirection("colonne", "afficherFormulaireMiseAJourColonne", ["idColonne" => $idColonne]);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurColonne::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("base", "accueil");
        }
    }
}