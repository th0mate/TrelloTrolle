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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurColonne extends ControleurGenerique
{
    public static function afficherErreur($messageErreur = "", $controleur = ""): Response
    {
        return parent::afficherErreur($messageErreur, "colonne");
    }

    #[Route('/colonne/supprimerColonne', name: 'supprimerColonne')]
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
        $tableau = $colonne->getTableau();

        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            ControleurColonne::redirection("afficherTableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $carteRepository = new CarteRepository();

        if($carteRepository->getNombreCartesTotalUtilisateur($tableau->getUtilisateur()->getLogin()) == 1) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas supprimer cette colonne car cela entrainera la supression du compte du propriétaire du tableau");
            ControleurColonne::redirection("afficherTableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        $colonneRepository->supprimer($idColonne);
        $colonneRepository = new ColonneRepository();
        if($colonneRepository->getNombreColonnesTotalTableau($tableau->getIdTableau()) > 0) {
            ControleurColonne::redirection("afficherTableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }

        ControleurCarte::redirection("afficherTableau", "afficherListeMesTableaux");
    }

    #[
        Route('/colonne/formulaireCreationColonne', name: 'afficherFormulaireCreationColonne')
    ]
    public static function afficherFormulaireCreationColonne(): Response {
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
        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            ControleurColonne::redirection("afficherTableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
      return  ControleurTableau::afficherVue('vueGenerale.php', [
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
        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            ControleurColonne::redirection("afficherTableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
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
        ControleurColonne::redirection("afficherTableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route('/colonne/formulaireMiseAJourColonne', name: 'afficherFormulaireMiseAJourColonne')]
    public static function afficherFormulaireMiseAJourColonne(): Response {
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
        $tableau = $colonne->getTableau();
        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            ControleurColonne::redirection("afficherTableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
      return ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Modification d'une colonne",
            "cheminVueBody" => "colonne/formulaireMiseAJourColonne.php",
            "idColonne" => $_REQUEST["idColonne"],
            "nomColonne" => $colonne->getTitreColonne()
        ]);
    }

    #[Route('/colonne/mettreAJourColonne', name: 'mettreAJourColonne')]
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
        $tableau = $colonne->getTableau();
        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            ControleurColonne::redirection("afficherTableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $colonne->setTitreColonne($_REQUEST["nomColonne"]);
        $colonneRepository->mettreAJour($colonne);
        ControleurColonne::redirection("afficherTableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }
}