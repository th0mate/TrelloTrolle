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
            (new ServiceConnexion())->connecter();
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
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            self::redirection("base", "accueil");
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        }
    }

    public static function afficherFormulaireCreationTableau(): void
    {
        try {
            (new ServiceConnexion())->connecter();
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

    public static function mettreAJourTableau(): void
    {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        $nomTableau = $_REQUEST["nomTableau"] ?? null;
        try {
            (new ServiceConnexion())->connecter();
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
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            self::redirection("base", "accueil");
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger",$e->getMessage());
            ControleurTableau::redirection("tableau", "afficherFormulaireMiseAJourTableau", ["idTableau" => $_REQUEST["idTableau"]]);
        }
    }

    public static function afficherFormulaireAjoutMembre(): void
    {
        $idTableau=$_REQUEST["idTableau"] ??null;
        try{
            (new ServiceConnexion())->connecter();
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
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger",$e->getMessage());
            self::redirection("base","accueil");
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger",$e->getMessage());
            ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        }
    }

    public static function ajouterMembre(): void
    {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        $login = $_REQUEST["login"] ?? null;
        try {
            (new ServiceConnexion())->connecter();
            $tableau = (new ServiceTableau())->recupererTableauParId($idTableau);
            (new ServiceUtilisateur())->ajouterMembre($tableau, $login);
            ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        } catch (ConnexionException $e) {
            self::redirectionConnectionFlash($e);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            self::redirection("base", "accueil");
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            self::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        }
    }

    public static function supprimerMembre(): void
    {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        $login = $_REQUEST["login"] ?? null;
        try {
            (new ServiceConnexion())->connecter();
            $tableau = (new ServiceTableau())->recupererTableauParId($idTableau);
            $utilisateur = (new ServiceUtilisateur())->supprimerMembre($tableau, $login);
            (new ServiceCarte())->miseAJourCarteMembre($tableau, $utilisateur);
            ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);

        } catch (ConnexionException $e) {
            self::redirectionConnectionFlash($e);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            self::redirection("base", "accueil");
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            self::redirection("tableau", "afficherTableau", ['codeTableau' => $e->getTableau()->getCodeTableau()]);
        }
    }

    public static function afficherListeMesTableaux(): void
    {
        try {
            (new ServiceConnexion())->connecter();
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
        if (!ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if (!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant du tableau manquant");
            ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
        }
        $repository = new TableauRepository();
        /**
         * @var Tableau $tableau
         */
        $tableau = $repository->recupererParClePrimaire($_REQUEST["idTableau"]);
        if (!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
        }

        $utilisateurRepository = new UtilisateurRepository();

        /**
         * @var Utilisateur $utilisateur
         */
        $utilisateur = $utilisateurRepository->recupererParClePrimaire(ConnexionUtilisateur::getLoginUtilisateurConnecte());
        if ($tableau->estProprietaire($utilisateur->getLogin())) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas quitter ce tableau");
            ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
        }
        if (!$tableau->estParticipant(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'appartenez pas à ce tableau");
            ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
        }
        $participants = array_filter($tableau->getParticipants(), function ($u) use ($utilisateur) {
            return $u->getLogin() !== $utilisateur->getLogin();
        });
        $tableau->setParticipants($participants);
        $repository->mettreAJour($tableau);

        $carteRepository = new CarteRepository();

        /**
         * @var Carte[] $cartes
         */
        $cartes = $carteRepository->recupererCartesTableau($tableau->getIdTableau());
        foreach ($cartes as $carte) {
            $affectations = array_filter($carte->getAffectationsCarte(), function ($u) use ($utilisateur) {
                return $u->getLogin() != $utilisateur->getLogin();
            });
            $carte->setAffectationsCarte($affectations);
            $carteRepository->mettreAJour($carte);
        }
        ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
    }

    public static function supprimerTableau(): void
    {
        if (!ConnexionUtilisateur::estConnecte()) {
            ControleurTableau::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if (!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant de tableau manquant");
            ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
        }
        $repository = new TableauRepository();
        $idTableau = $_REQUEST["idTableau"];
        /**
         * @var Tableau $tableau
         */
        $tableau = $repository->recupererParClePrimaire($idTableau);
        if (!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
        }
        if (!$tableau->estProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'êtes pas propriétaire de ce tableau");
            ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
        }
        if ($repository->getNombreTableauxTotalUtilisateur(ConnexionUtilisateur::getLoginUtilisateurConnecte()) == 1) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas supprimer ce tableau car cela entrainera la supression du compte");
            ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
        }
        $repository->supprimer($idTableau);
        ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
    }
}