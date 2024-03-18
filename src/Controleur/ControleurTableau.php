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
use App\Trellotrolle\Service\Exception\TableauException;
use App\Trellotrolle\Service\ServiceCarte;
use App\Trellotrolle\Service\ServiceConnexion;
use App\Trellotrolle\Service\ServiceTableau;
use App\Trellotrolle\Service\ServiceUtilisateur;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ControleurTableau extends ControleurGenerique
{
    public static function afficherErreur($messageErreur = "", $controleur = ""): Response
    {
        return parent::afficherErreur($messageErreur, "tableau");
    }

    #[Route('/tableau/monTableau', name: 'afficherTableau',methods: "GET")]
    public static function afficherTableau(): Response
    {;
        $codeTableau = $_REQUEST["codeTableau"] ?? null;
        try {
            $tableau = (new ServiceTableau())->recupererTableauParCode($codeTableau);
            $donnes = (new ServiceTableau())->recupererCartesColonnes($tableau);
            return ControleurTableau::afficherVue('vueGenerale.php', [
                "pagetitle" => "{$tableau->getTitreTableau()}",
                "cheminVueBody" => "tableau/tableau.php",
                "tableau" => $tableau,
                "colonnes" => $donnes["colonnes"],
                "participants" => $donnes["participants"],
                "data" => $donnes["data"],
            ]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection( 'accueil');
        }


    }

    #[Route('/tableau/mettreAJour', name: 'afficherFormulaireMiseAJourTableau',methods: "GET")]
    public static function afficherFormulaireMiseAJourTableau(): Response
    {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        try {
            (new ServiceConnexion())->pasConnecter();
            $tableau = (new ServiceTableau())->recupererTableauParId($idTableau);
            (new ServiceUtilisateur())->estParticipant($tableau);
            return ControleurTableau::afficherVue('vueGenerale.php', [
                "pagetitle" => "Modification d'un tableau",
                "cheminVueBody" => "tableau/formulaireMiseAJourTableau.php",
                "idTableau" => $_REQUEST["idTableau"],
                "nomTableau" => $tableau->getTitreTableau()
            ]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurTableau::redirection( "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection( "accueil");
        }
    }

    #[Route('/tableau/nouveau', name: 'afficherFormulaireCreationTableau',methods: "GET")]
    public static function afficherFormulaireCreationTableau(): Response
    {
        try {
            (new ServiceConnexion())->pasConnecter();
            return ControleurTableau::afficherVue('vueGenerale.php', [
                "pagetitle" => "Ajout d'un tableau",
                "cheminVueBody" => "tableau/formulaireCreationTableau.php",
            ]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        }
    }

    #[Route('/tableau/nouveau', name: 'creerTableau',methods: "POST")]
    public static function creerTableau(): Response
    {
        $nomTableau=$_REQUEST["nomTableau"] ??null;
        try{
            (new ServiceConnexion())->pasConnecter();
            $tableau=(new ServiceTableau())->creerTableau($nomTableau);
            return ControleurTableau::redirection( "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);

        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger",$e->getMessage());
            return self::redirection("afficherFormulaireCreationTableau");
        }
    }

    #[Route('/tableau/mettreAJour', name: 'mettreAJourTableau',methods: "POST")]
    public static function mettreAJourTableau(): Response
    {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        $nomTableau = $_REQUEST["nomTableau"] ?? null;
        try {
            (new ServiceConnexion())->pasConnecter();
            $tableau = (new ServiceTableau())->recupererTableauParId($idTableau);
            (new ServiceTableau())->isNotNullNomTableau($nomTableau,$tableau);
            if (!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
                MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            } else {
                $tableau->setTitreTableau($_REQUEST["nomTableau"]);
                (new ServiceTableau())->mettreAJourTableau($tableau);
            }
            return ControleurTableau::redirection( "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        }catch (TableauException $e) {
            MessageFlash::ajouter("danger",$e->getMessage());
            return ControleurTableau::redirection( "afficherFormulaireMiseAJourTableau", ["idTableau" => $_REQUEST["idTableau"]]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection( "accueil");
        }
    }

    #[Route('/tableau/inviter', name: 'afficherFormulaireAjoutMembre',methods: "GET")]
    public static function afficherFormulaireAjoutMembre(): Response
    {
        $idTableau=$_REQUEST["idTableau"] ??null;
        try{
            (new ServiceConnexion())->pasConnecter();
            $tableau=(new ServiceTableau())->recupererTableauParId($idTableau);
            $filtredUtilisateurs=(new ServiceUtilisateur())->verificationsMembre($tableau,ConnexionUtilisateur::getLoginUtilisateurConnecte());
            return ControleurTableau::afficherVue('vueGenerale.php', [
                "pagetitle" => "Ajout d'un membre",
                "cheminVueBody" => "tableau/formulaireAjoutMembreTableau.php",
                "tableau" => $tableau,
                "utilisateurs" => $filtredUtilisateurs
            ]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger",$e->getMessage());
            return ControleurTableau::redirection( "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        }catch (ServiceException $e) {
            MessageFlash::ajouter("danger",$e->getMessage());
            return self::redirection("accueil");
        }
    }

    #[Route('/tableau/inviter', name: 'ajouterMembreTableau',methods: "POST")]
    public static function ajouterMembre(): Response
    {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        $login = $_REQUEST["login"] ?? null;
        try {
            (new ServiceConnexion())->pasConnecter();
            $tableau = (new ServiceTableau())->recupererTableauParId($idTableau);
            (new ServiceUtilisateur())->ajouterMembre($tableau, $login);
            return ControleurTableau::redirection( "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection( "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection( "accueil");
        }
    }

    #[Route('/tableau/supprimerMembre', name: 'supprimerMembre',methods: "GET")]
    public static function supprimerMembre(): Response
    {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        $login = $_REQUEST["login"] ?? null;
        try {
            (new ServiceConnexion())->pasConnecter();
            $tableau = (new ServiceTableau())->recupererTableauParId($idTableau);
            $utilisateur = (new ServiceUtilisateur())->supprimerMembre($tableau, $login);
            (new ServiceCarte())->miseAJourCarteMembre($tableau, $utilisateur);
            return ControleurTableau::redirection( "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);

        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        }  catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection( "afficherTableau", ['codeTableau' => $e->getTableau()->getCodeTableau()]);
        }catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection( "accueil");
        }
    }

    #[Route('/tableau', name: 'afficherListeMesTableaux',methods: "GET")]
    public static function afficherListeMesTableaux(): Response
    {
        try {
            (new ServiceConnexion())->pasConnecter();
            $login = ConnexionUtilisateur::getLoginUtilisateurConnecte();
            $tableaux = (new ServiceTableau())->recupererTableauEstMembre($login);
            return ControleurTableau::afficherVue('vueGenerale.php', [
                "pagetitle" => "Liste des tableaux de $login",
                "cheminVueBody" => "tableau/listeTableauxUtilisateur.php",
                "tableaux" => $tableaux
            ]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        }
    }

    #[Route('/tableau/quitter', name: 'quitterTableau',methods: "GET")]
    public static function quitterTableau(): Response
    {
        $idTableau=$_REQUEST["idTableau"] ??null;
        try{
            (new ServiceConnexion())->pasConnecter();
            $tableau=(new ServiceTableau())->recupererTableauParId($idTableau);
            $utilisateur=(new ServiceUtilisateur())->recupererUtilisateurParCle(ConnexionUtilisateur::getLoginUtilisateurConnecte());
            (new ServiceTableau())->quitterTableau($tableau,$utilisateur);
            return ControleurTableau::redirection( "afficherListeMesTableaux");

        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger",$e->getMessage());
            return self::redirection("afficherListeMesTableaux");
        }
    }

    #[Route('/tableau/suppression', name: 'supprimerTableau',methods: "GET")]
    public static function supprimerTableau(): Response
    {
        $idTableau=$_REQUEST["idTableau"] ??null;
        try{
            (new ServiceConnexion())->pasConnecter();
            $tableau=(new ServiceTableau())->recupererTableauParId($idTableau);
            (new ServiceUtilisateur())->estProprietaire($tableau,ConnexionUtilisateur::getLoginUtilisateurConnecte());
            (new ServiceTableau())->supprimerTableau($idTableau);
            return ControleurTableau::redirection( "afficherListeMesTableaux");
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger",$e->getMessage());
            return self::redirection("afficherListeMesTableaux");
        }
    }
}