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

class ControleurColonne extends ControleurGenerique
{
    public static function afficherErreur($messageErreur = "", $controleur = ""): void
    {
        parent::afficherErreur($messageErreur, "colonne");
    }

    public static function supprimerColonne(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurColonne::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idColonne"])) {
            MessageFlash::ajouter("danger", "Code de colonne manquant");
            ControleurColonne::redirection("base", "accueil");
        }
        $colonneRepository = new ColonneRepository();
        $idColonne = $_REQUEST["idColonne"];
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonneRepository->recupererParClePrimaire($idColonne);
        if(!$colonne) {
            MessageFlash::ajouter("danger", "Colonne inexistante");
            ControleurColonne::redirection("base", "accueil");
        }
        $tableau = ColonneRepository::getTableau($colonne);

        if(!TableauRepository::estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte(), $tableau)) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            ControleurColonne::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $carteRepository = new CarteRepository();

        if($carteRepository->getNombreCartesTotalUtilisateur(TableauRepository::getUtilisateur($tableau)->getLogin()) == 1) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas supprimer cette colonne car cela entrainera la supression du compte du propriétaire du tableau");
            ControleurColonne::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        $colonneRepository->supprimer($idColonne);
        $colonneRepository = new ColonneRepository();
        if($colonneRepository->getNombreColonnesTotalTableau($tableau->getIdTableau()) > 0) {
            ControleurColonne::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        ControleurCarte::redirection("tableau", "afficherListeMesTableaux");
    }

    public static function afficherFormulaireCreationColonne(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurColonne::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("warning", "Identifiant du tableau manquant");
            ControleurColonne::redirection("base", "accueil");
        }
        $repository = new TableauRepository();
        /**
         * @var Tableau $tableau
         */
        $tableau = $repository->recupererParClePrimaire($_REQUEST["idTableau"]);
        if(!$tableau) {
            MessageFlash::ajouter("warning", "Tableau inexistant");
            ControleurColonne::redirection("base", "accueil");
        }
        if(!TableauRepository::estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte(), $tableau)) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            ControleurColonne::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Création d'une colonne",
            "cheminVueBody" => "colonne/formulaireCreationColonne.php",
            "idTableau" => $_REQUEST["idTableau"],
        ]);
    }

    public static function creerColonne(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurColonne::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant du tableau manquant");
            ControleurColonne::redirection("base", "accueil");
        }
        $repository = new TableauRepository();
        /**
         * @var Tableau $tableau
         */
        $tableau = $repository->recupererParClePrimaire($_REQUEST["idTableau"]);
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            ControleurColonne::redirection("base", "accueil");
        }
        if(!ControleurCarte::issetAndNotNull(["nomColonne"])) {
            MessageFlash::ajouter("danger", "Nom de colonne manquant");
            ControleurColonne::redirection("colonne", "afficherFormulaireCreationColonne", ["idTableau" => $_REQUEST["idTableau"]]);
        }
        if(!TableauRepository::estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte(), $tableau)) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            ControleurColonne::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $colonneRepository = new ColonneRepository();
        $carteRepository = new CarteRepository();
        $colonne = new Colonne(
            $tableau,
            $colonneRepository->getNextIdColonne(),
            $_REQUEST["nomColonne"]
        );
        $carte = new Carte(
            $colonne,
            $carteRepository->getNextIdCarte(),
            "Exemple",
            "Exemple de carte",
            "#FFFFFF",
            []
        );
        $carteRepository->ajouter($carte);
        ControleurColonne::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    public static function afficherFormulaireMiseAJourColonne(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurColonne::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idColonne"])) {
            MessageFlash::ajouter("danger", "Identifiant du colonne manquant");
            ControleurColonne::redirection("base", "accueil");
        }
        $colonneRepository = new ColonneRepository();
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonneRepository->recupererParClePrimaire($_REQUEST["idColonne"]);
        if(!$colonne) {
            MessageFlash::ajouter("danger", "Colonne inexistante");
            ControleurColonne::redirection("base", "accueil");
        }
        $tableau = ColonneRepository::getTableau($colonne);
        if(!TableauRepository::estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte(), $tableau)) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            ControleurColonne::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Modification d'une colonne",
            "cheminVueBody" => "colonne/formulaireMiseAJourColonne.php",
            "idColonne" => $_REQUEST["idColonne"],
            "nomColonne" => $colonne->getTitreColonne()
        ]);
    }

    public static function mettreAJourColonne(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            ControleurColonne::redirection("utilisateur", "afficherFormulaireConnexion");
        }
        if(!ControleurCarte::issetAndNotNull(["idColonne"])) {
            MessageFlash::ajouter("danger", "Identifiant du colonne manquant");
            ControleurColonne::redirection("base", "accueil");
        }
        $colonneRepository = new ColonneRepository();
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonneRepository->recupererParClePrimaire($_REQUEST["idColonne"]);
        if(!$colonne) {
            MessageFlash::ajouter("danger", "Colonne inexistante");
            ControleurColonne::redirection("base", "accueil");
        }
        if(!ControleurCarte::issetAndNotNull(["nomColonne"])) {
            MessageFlash::ajouter("danger", "Nom de colonne manquant");
            ControleurColonne::redirection("colonne", "afficherFormulaireMiseAJourColonne", ["idColonne" => $_REQUEST["idColonne"]]);
        }
        $tableau = ColonneRepository::getTableau($colonne);
        if(!TableauRepository::estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte(), $tableau)) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            ControleurColonne::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $colonne->setTitreColonne($_REQUEST["nomColonne"]);
        $colonneRepository->mettreAJour($colonne);
        ControleurColonne::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }
}