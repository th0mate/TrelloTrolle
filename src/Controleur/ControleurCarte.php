<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Response;


class ControleurCarte extends ControleurGenerique
{
    public static function afficherErreur($messageErreur = "", $controleur = ""): Response
    {
        return parent::afficherErreur($messageErreur, "carte");
    }

    public static function supprimerCarte(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurCarte::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idCarte"])) {
            MessageFlash::ajouter("warning", "Code de carte manquant");
            ControleurCarte::redirection("base", "accueil");
        }
        $carteRepository = new CarteRepository();
        $idCarte = $_REQUEST["idCarte"];
        /**
         * @var Carte $carte
         */
        $carte = $carteRepository->recupererParClePrimaire($idCarte);
        if(!$carte) {
            MessageFlash::ajouter("danger", "Carte inexistante");
            ControleurCarte::redirection("base", "accueil");
        }

        $tableau = $carte->getColonne()->getTableau();

        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        if($carteRepository->getNombreCartesTotalUtilisateur($tableau->getUtilisateur()->getLogin()) == 1) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas supprimer cette carte car cela entrainera la supression du compte du propriétaire du tableau");
            ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $carteRepository->supprimer($idCarte);
        $cartes = $carteRepository->recupererCartesTableau($tableau->getIdTableau());
        if(count($cartes) > 0) {
            ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        else {
            ControleurCarte::redirection("tableau", "afficherListeMesTableaux");
        }
    }

    public static function afficherFormulaireCreationCarte(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurCarte::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idColonne"])) {
            MessageFlash::ajouter("warning", "Identifiant de colonne manquant");
            ControleurCarte::redirection("base", "accueil");
        }
        $colonneRepository = new ColonneRepository();
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonneRepository->recupererParClePrimaire($_REQUEST["idColonne"]);
        if(!$colonne) {
            MessageFlash::ajouter("warning", "Colonne inexistante");
            ControleurCarte::redirection("base", "accueil");
        }
        $tableau = $colonne->getTableau();
        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $colonnes = $colonneRepository->recupererColonnesTableau($tableau->getIdTableau());
        ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Création d'une carte",
            "cheminVueBody" => "carte/formulaireCreationCarte.php",
            "colonne" => $colonne,
            "colonnes" => $colonnes
        ]);
    }

    public static function creerCarte(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurCarte::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idColonne"])) {
            MessageFlash::ajouter("warning", "Identifiant de colonne manquant");
            ControleurCarte::redirection("base", "accueil");
        }
        $colonneRepository = new ColonneRepository();
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonneRepository->recupererParClePrimaire($_REQUEST["idColonne"]);
        if(!$colonne) {
            MessageFlash::ajouter("warning", "Colonne inexistante");
            ControleurCarte::redirection("base", "accueil");
        }
        if(!ControleurCarte::issetAndNotNull(["titreCarte", "descriptifCarte", "couleurCarte"])) {
            MessageFlash::ajouter("danger", "Attributs manquants");
            ControleurColonne::redirection("carte", "afficherFormulaireCreationCarte", ["idColonne" => $_REQUEST["idColonne"]]);
        }
        $tableau = $colonne->getTableau();
        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $affectations = [];
        $utilisateurRepository = new UtilisateurRepository();
        if(ControleurCarte::issetAndNotNull(["affectationsCarte"])) {
            foreach ($_REQUEST["affectationsCarte"] as $affectation) {
                /**
                 * @var Utilisateur $utilisateur
                 */
                $utilisateur = $utilisateurRepository->recupererParClePrimaire($affectation);
                if(!$utilisateur) {
                    MessageFlash::ajouter("danger", "Un des membres affecté à la tâche n'existe pas");
                    ControleurCarte::redirection("carte", "afficherFormulaireCreationCarte", ["idColonne" => $_REQUEST["idColonne"]]);
                }
                if(!$tableau->estParticipantOuProprietaire($utilisateur->getLogin())) {
                    MessageFlash::ajouter("danger", "Un des membres affecté à la tâche n'est pas affecté au tableau.");
                    ControleurCarte::redirection("carte", "afficherFormulaireCreationCarte", ["idColonne" => $_REQUEST["idColonne"]]);
                }
                $affectations[] = $utilisateur;
            }
        }
        $carteRepository = new CarteRepository();
        $carte = new Carte(
            $colonne,
            $carteRepository->getNextIdCarte(),
            $_REQUEST["titreCarte"],
            $_REQUEST["descriptifCarte"],
            $_REQUEST["couleurCarte"],
            $affectations
        );
        $carteRepository->ajouter($carte);
        ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    public static function afficherFormulaireMiseAJourCarte(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurCarte::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idCarte"])) {
            MessageFlash::ajouter("warning", "Identifiant de la carte manquant");
            ControleurCarte::redirection("base", "accueil");
        }
        $carteRepository = new CarteRepository();
        /**
         * @var Carte $carte
         */
        $carte = $carteRepository->recupererParClePrimaire($_REQUEST["idCarte"]);
        if(!$carte) {
            MessageFlash::ajouter("warning", "Carte inexistante");
            ControleurCarte::redirection("base", "accueil");
        }
        $tableau = $carte->getColonne()->getTableau();
        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $colonneRepository = new ColonneRepository();
        $colonnes = $colonneRepository->recupererColonnesTableau($tableau->getIdTableau());
        ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Modification d'une carte",
            "cheminVueBody" => "carte/formulaireMiseAJourCarte.php",
            "carte" => $carte,
            "colonnes" => $colonnes
        ]);
    }

    public static function mettreAJourCarte(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurCarte::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idCarte"])) {
            MessageFlash::ajouter("warning", "Identifiant de la carte manquant");
            ControleurCarte::redirection("base", "accueil");
        }
        if(!ControleurCarte::issetAndNotNull(["idColonne"])) {
            MessageFlash::ajouter("warning", "Identifiant de colonne manquant");
            ControleurCarte::redirection("base", "accueil");
        }
        $carteRepository = new CarteRepository();
        /**
         * @var Carte $carte
         */
        $carte = $carteRepository->recupererParClePrimaire($_REQUEST["idCarte"]);

        $colonnesRepository = new ColonneRepository();
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonnesRepository->recupererParClePrimaire($_REQUEST["idColonne"]);
        if(!$carte) {
            MessageFlash::ajouter("warning", "Carte inexistante");
            ControleurCarte::redirection("base", "accueil");
        }
        if(!$colonne) {
            MessageFlash::ajouter("warning", "Colonne inexistante");
            ControleurCarte::redirection("base", "accueil");
        }
        if(!ControleurCarte::issetAndNotNull(["titreCarte", "descriptifCarte", "couleurCarte"])) {
            MessageFlash::ajouter("danger", "Attributs manquants");
            ControleurColonne::redirection("carte", "afficherFormulaireMiseAJourCarte", ["idCarte" => $_REQUEST["idCarte"]]);
        }

        $originalColonne = $carte->getColonne();
        if($originalColonne->getTableau()->getIdTableau() !== $colonne->getTableau()->getIdTableau()) {
            MessageFlash::ajouter("danger", "Le tableau de cette colonne n'est pas le même que celui de la colonne d'origine de la carte!");
            ControleurColonne::redirection("carte", "afficherFormulaireMiseAJourCarte", ["idCarte" => $_REQUEST["idCarte"]]);
        }
        $tableau = $colonne->getTableau();
        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        $carte->setColonne($colonne);
        $carte->setTitreCarte($_REQUEST["titreCarte"]);
        $carte->setDescriptifCarte($_REQUEST["descriptifCarte"]);
        $carte->setCouleurCarte($_REQUEST["couleurCarte"]);
        $affectations = [];
        $utilisateurRepository = new UtilisateurRepository();
        if(ControleurCarte::issetAndNotNull(["affectationsCarte"])) {
            foreach ($_REQUEST["affectationsCarte"] as $affectation) {
                /**
                 * @var Utilisateur $utilisateur
                 */
                $utilisateur = $utilisateurRepository->recupererParClePrimaire($affectation);
                if(!$utilisateur) {
                    MessageFlash::ajouter("danger", "Un des membres affecté à la tâche n'existe pas");
                    ControleurCarte::redirection("carte", "afficherFormulaireMiseAJourCarte", ["idCarte" => $_REQUEST["idCarte"]]);
                }
                if(!$tableau->estParticipantOuProprietaire($utilisateur->getLogin())) {
                    MessageFlash::ajouter("danger", "Un des membres affecté à la tâche n'est pas affecté au tableau.");
                    ControleurCarte::redirection("carte", "afficherFormulaireCreationCarte", ["idColonne" => $_REQUEST["idColonne"]]);
                }
                $affectations[] = $utilisateur;
            }
        }
        $carte->setAffectationsCarte($affectations);
        $carteRepository->mettreAJour($carte);
        ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }
}