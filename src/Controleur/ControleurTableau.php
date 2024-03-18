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
use Symfony\Component\Routing\Annotation\Route;

class ControleurTableau extends ControleurGenerique
{
    public static function afficherErreur($messageErreur = "", $controleur = ""): void
    {
        parent::afficherErreur($messageErreur, "tableau");
    }

    #[Route('/tableau/afficherTableau', name: 'afficherTableau')]
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

    #[Route('/tableau/afficherFormulaireMiseAJourTableau', name: 'afficherFormulaireMiseAJourTableau')]
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

    #[Route('/tableau/afficherFormulaireCreationTableau', name: 'afficherFormulaireCreationTableau')]
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

    #[Route('/tableau/creerTableau', name: 'creerTableau')]
    public static function creerTableau(): void
    {
        if (!ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        $utilisateurRepository = new UtilisateurRepository();

        /**
         * @var Utilisateur $utilisateur
         */
        $utilisateur = $utilisateurRepository->recupererParClePrimaire(ConnexionUtilisateur::getLoginUtilisateurConnecte());
        if (!ControleurCarte::issetAndNotNull(["nomTableau"])) {
            MessageFlash::ajouter("danger", "Nom de tableau manquant");
            ControleurTableau::redirection("tableau", "afficherFormulaireCreationTableau");
        }
        $tableauRepository = new TableauRepository();
        $idTableau = $tableauRepository->getNextIdTableau();
        $codeTableau = hash("sha256", $utilisateur->getLogin() . $idTableau);

        $colonneRepository = new ColonneRepository();
        $idColonne1 = $colonneRepository->getNextIdColonne();
        $nomColonne1 = "TODO";

        $nomColonne2 = "DOING";
        $idColonne2 = $idColonne1 + 1;

        $nomColonne3 = "DONE";
        $idColonne3 = $idColonne1 + 2;

        $carteInitiale = "Exemple";
        $descriptifInitial = "Exemple de carte";

        $carteRepository = new CarteRepository();

        $idCarte1 = $carteRepository->getNextIdCarte();
        $idCarte2 = $idCarte1 + 1;
        $idCarte3 = $idCarte1 + 2;

        $tableau = new Tableau(
            $utilisateur,
            $idTableau,
            $codeTableau,
            $_REQUEST["nomTableau"],
            []
        );

        $carte1 = new Carte(
            new Colonne(
                $tableau,
                $idColonne1,
                $nomColonne1
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
                $nomColonne2
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
                $nomColonne3
            ),
            $idCarte3,
            $carteInitiale,
            $descriptifInitial,
            "#FFFFFF",
            []
        );

        $carteRepository->ajouter($carte1);
        $carteRepository->ajouter($carte2);
        $carteRepository->ajouter($carte3);

        ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route('/tableau/mettreAJourTableau', name: 'mettreAJourTableau')]
    public static function mettreAJourTableau(): void
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

    #[Route('/tableau/formulaireAjoutMembreTableau', name: 'afficherFormulaireAjoutMembre')]
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

    #[Route('/tableau/ajouterMembre', name: 'ajouterMembreTableau')]
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

    #[Route('/tableau/supprimerMembre', name: 'supprimerMembre')]
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

    #[Route('/tableau/afficherListeMesTableaux', name: 'afficherListeMesTableaux')]
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

    #[Route('/tableau/afficherListeTableaux', name: 'afficherListeTableaux')]
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

    #[Route('/tableau/supprimerTableau', name: 'supprimerTableau')]
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