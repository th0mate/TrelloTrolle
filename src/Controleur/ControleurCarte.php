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
use Symfony\Component\Routing\Attribute\Route;


class ControleurCarte extends ControleurGenerique
{
    public static function afficherErreur($messageErreur = "", $controleur = ""): Response
    {
        return parent::afficherErreur($messageErreur, "carte");
    }

    #[Route('../carte/supprimerCarte', name: 'supprimerCarte')]
    public static function supprimerCarte(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            (new ControleurCarte)->redirection("utilisateur", ["action"=>"afficherFormulaireConnexion"]);
        }
        if(!ControleurCarte::issetAndNotNull(["idCarte"])) {
            MessageFlash::ajouter("warning", "Code de carte manquant");
            (new ControleurCarte)->redirection("base", ["action"=>"accueil"]);
        }
        $carteRepository = new CarteRepository();
        $idCarte = $_REQUEST["idCarte"];
        /**
         * @var Carte $carte
         */
        $carte = $carteRepository->recupererParClePrimaire($idCarte);
        if(!$carte) {
            MessageFlash::ajouter("danger", "Carte inexistante");
            (new ControleurCarte)->redirection("base", ["action"=>"accueil"]);
        }

        $tableau = $carte->getColonne()->getTableau();

        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            (new ControleurCarte)->redirection("tableau", ["action"=>"tableau"], ["codeTableau" => $tableau->getCodeTableau()]);
        }
        if($carteRepository->getNombreCartesTotalUtilisateur($tableau->getUtilisateur()->getLogin()) == 1) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas supprimer cette carte car cela entrainera la supression du compte du propriétaire du tableau");
            (new ControleurCarte)->redirection("tableau", ["action"=>"tableau"], ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $carteRepository->supprimer($idCarte);
        $cartes = $carteRepository->recupererCartesTableau($tableau->getIdTableau());
        if(count($cartes) > 0) {
            (new ControleurCarte)->redirection("tableau", ["action"=>"tableau"], ["codeTableau" => $tableau->getCodeTableau()]);
        }
        else {
            (new ControleurCarte)->redirection("tableau", ["action"=>"afficherListeMesTableaux"]);
        }
    }


    #[Route('carte/formulaireCreationCarte', name: 'afficherFormulaireCreationCarte')]
    public static function afficherFormulaireCreationCarte(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            (new ControleurCarte)->redirection("utilisateur", ["action"=>"afficherFormulaireConnexion"]);
        }
        if(!ControleurCarte::issetAndNotNull(["idColonne"])) {
            MessageFlash::ajouter("warning", "Identifiant de colonne manquant");
            (new ControleurCarte)->redirection("base", ["action"=>"accueil"]);
        }
        $colonneRepository = new ColonneRepository();
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonneRepository->recupererParClePrimaire($_REQUEST["idColonne"]);
        if(!$colonne) {
            MessageFlash::ajouter("warning", "Colonne inexistante");
            (new ControleurCarte)->redirection("base", ["action"=>"accueil"]);
        }
        $tableau = $colonne->getTableau();
        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            (new ControleurCarte)->redirection("tableau", ["action"=>"tableau"], ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $colonnes = $colonneRepository->recupererColonnesTableau($tableau->getIdTableau());
        return ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Création d'une carte",
            "cheminVueBody" => "carte/formulaireCreationCarte.php",
            "colonne" => $colonne,
            "colonnes" => $colonnes
        ]);
    }


    #[Route('../carte/creerCarte', name: 'creerCarte')]
    public static function creerCarte(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            (new ControleurCarte)->redirection("utilisateur", [["action"=>"afficherFormulaireConnexion"]]);
        }
        if(!ControleurCarte::issetAndNotNull(["idColonne"])) {
            MessageFlash::ajouter("warning", "Identifiant de colonne manquant");
            (new ControleurCarte)->redirection("base", ["action" => ["action"=>"accueil"]]);
        }
        $colonneRepository = new ColonneRepository();
        /**
         * @var Colonne $colonne
         */
        $colonne = $colonneRepository->recupererParClePrimaire($_REQUEST["idColonne"]);
        if(!$colonne) {
            MessageFlash::ajouter("warning", "Colonne inexistante");
            (new ControleurCarte)->redirection("base", ["action"=>"accueil"]);
        }
        if(!ControleurCarte::issetAndNotNull(["titreCarte", "descriptifCarte", "couleurCarte"])) {
            MessageFlash::ajouter("danger", "Attributs manquants");
            (new ControleurColonne)->redirection("carte", ["action"=>"afficherFormulaireCreationCarte"], ["idColonne" => $_REQUEST["idColonne"]]);
        }
        $tableau = $colonne->getTableau();
        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            (new ControleurCarte)->redirection("tableau", ["action"=>"tableau"], ["codeTableau" => $tableau->getCodeTableau()]);
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
                    (new ControleurCarte)->redirection("carte", ["action"=>"afficherFormulaireCreationCarte"], ["idColonne" => $_REQUEST["idColonne"]]);
                }
                if(!$tableau->estParticipantOuProprietaire($utilisateur->getLogin())) {
                    MessageFlash::ajouter("danger", "Un des membres affecté à la tâche n'est pas affecté au tableau.");
                    (new ControleurCarte)->redirection("carte", ["action"=>"afficherFormulaireCreationCarte"], ["idColonne" => $_REQUEST["idColonne"]]);
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
        (new ControleurCarte)->redirection("tableau", ["action"=>"tableau"], ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route('carte/formulaireMiseAJourCarte', name: 'afficherFormulaireMiseAJourCarte')]
    public static function afficherFormulaireMiseAJourCarte(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            (new ControleurCarte)->redirection("utilisateur", ["action"=>"afficherFormulaireConnexion"]);
        }
        if(!ControleurCarte::issetAndNotNull(["idCarte"])) {
            MessageFlash::ajouter("warning", "Identifiant de la carte manquant");
            (new ControleurCarte)->redirection("base", ["action"=>"accueil"]);
        }
        $carteRepository = new CarteRepository();
        /**
         * @var Carte $carte
         */
        $carte = $carteRepository->recupererParClePrimaire($_REQUEST["idCarte"]);
        if(!$carte) {
            MessageFlash::ajouter("warning", "Carte inexistante");
            (new ControleurCarte)->redirection("base", ["action"=>"accueil"]);
        }
        $tableau = $carte->getColonne()->getTableau();
        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            (new ControleurCarte)->redirection("tableau", ["action"=>"tableau"], ["codeTableau" => $tableau->getCodeTableau()]);
        }
        $colonneRepository = new ColonneRepository();
        $colonnes = $colonneRepository->recupererColonnesTableau($tableau->getIdTableau());
       return ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Modification d'une carte",
            "cheminVueBody" => "carte/formulaireMiseAJourCarte.php",
            "carte" => $carte,
            "colonnes" => $colonnes
        ]);
    }

    #[Route('../carte/mettreAJourCarte', name: 'mettreAJourCarte')]
    public static function mettreAJourCarte(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
          return  (new ControleurCarte)->redirection("utilisateur", ["action"=>"afficherFormulaireConnexion"]);
        }
        if(!ControleurCarte::issetAndNotNull(["idCarte"])) {
            MessageFlash::ajouter("warning", "Identifiant de la carte manquant");
            return (new ControleurCarte)->redirection("base", ["action"=>"accueil"]);
        }
        if(!ControleurCarte::issetAndNotNull(["idColonne"])) {
            MessageFlash::ajouter("warning", "Identifiant de colonne manquant");
            return  (new ControleurCarte)->redirection("base", ["action"=>"accueil"]);
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
            return  (new ControleurCarte)->redirection("base", ["action"=>"accueil"]);
        }
        if(!$colonne) {
            MessageFlash::ajouter("warning", "Colonne inexistante");
            return  (new ControleurCarte)->redirection("base", ["action"=>"accueil"]);
        }
        if(!ControleurCarte::issetAndNotNull(["titreCarte", "descriptifCarte", "couleurCarte"])) {
            MessageFlash::ajouter("danger", "Attributs manquants");
            return  (new ControleurColonne)->redirection("carte", ["action"=>"afficherFormulaireMiseAJourCarte"], ["idCarte" => $_REQUEST["idCarte"]]);
        }

        $originalColonne = $carte->getColonne();
        if($originalColonne->getTableau()->getIdTableau() !== $colonne->getTableau()->getIdTableau()) {
            MessageFlash::ajouter("danger", "Le tableau de cette colonne n'est pas le même que celui de la colonne d'origine de la carte!");
            return   (new ControleurColonne)->redirection("carte", ["action"=>"afficherFormulaireMiseAJourCarte"], ["idCarte" => $_REQUEST["idCarte"]]);
        }
        $tableau = $colonne->getTableau();
        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            return  (new ControleurCarte)->redirection("tableau", ["action"=>"tableau"], ["codeTableau" => $tableau->getCodeTableau()]);
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
                    return (new ControleurCarte)->redirection("carte", ["action"=>"afficherFormulaireMiseAJourCarte"], ["idCarte" => $_REQUEST["idCarte"]]);
                }
                if(!$tableau->estParticipantOuProprietaire($utilisateur->getLogin())) {
                    MessageFlash::ajouter("danger", "Un des membres affecté à la tâche n'est pas affecté au tableau.");
                    return  (new ControleurCarte)->redirection("carte", ["action"=>"afficherFormulaireCreationCarte"], ["idColonne" => $_REQUEST["idColonne"]]);
                }
                $affectations[] = $utilisateur;
            }
        }
        $carte->setAffectationsCarte($affectations);
        $carteRepository->mettreAJour($carte);
        return   (new ControleurCarte)->redirection("tableau", ["action"=>"tableau"], ["codeTableau" => $tableau->getCodeTableau()]);
    }
}