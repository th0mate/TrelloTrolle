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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class ControleurTableau extends ControleurGenerique
{

    public function __construct()
    {
    }

    
    public static function afficherErreur($messageErreur = "", $controleur = ""): Response
    {
       return parent::afficherErreur($messageErreur, "tableau");
    }

    #[Route('/tableau/tableau', name: 'afficherTableau')]
    public static function afficherTableau() : Response {
        if(!ControleurTableau::issetAndNotNull(["codeTableau"])) {
            MessageFlash::ajouter("warning", "Code de tableau manquant");
          return  (new ControleurTableau)->redirection("base", ["action" => "../accueil"]);
        }
        $code = $_REQUEST["codeTableau"];
        $tableauRepository = new TableauRepository();

        /**
         * @var Tableau $tableau
         */
        $tableau = $tableauRepository->recupererParCodeTableau($code);
        if(!$tableau) {
            MessageFlash::ajouter("warning", "Tableau inexistant");
         return   (new ControleurTableau)->redirection("base", ["action" => "../accueil"]);
        }
        $colonneRepository = new ColonneRepository();

        /**
         * @var Colonne[] $colonnes
         */
        $colonnes = $colonneRepository->recupererColonnesTableau($tableau->getIdTableau());
        $data = [];
        $participants = [];

        $carteRepository = new CarteRepository();
        foreach ($colonnes as $colonne) {
            /**
             * @var Carte[] $cartes
             */
            $cartes = $carteRepository->recupererCartesColonne($colonne->getIdColonne());
            foreach ($cartes as $carte) {
                foreach ($carte->getAffectationsCarte() as $utilisateur) {
                    if(!isset($participants[$utilisateur->getLogin()])) {
                        $participants[$utilisateur->getLogin()] = ["infos" => $utilisateur, "colonnes" => []];
                    }
                    if(!isset($participants[$utilisateur->getLogin()]["colonnes"][$colonne->getIdColonne()])) {
                        $participants[$utilisateur->getLogin()]["colonnes"][$colonne->getIdColonne()] = [$colonne->getTitreColonne(), 0];
                    }
                    $participants[$utilisateur->getLogin()]["colonnes"][$colonne->getIdColonne()][1]++;
                }
            }
            $data[] = $cartes;
        }

       return ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "{$tableau->getTitreTableau()}",
            "cheminVueBody" => "tableau/tableau.php",
            "tableau" => $tableau,
            "colonnes" => $colonnes,
            "participants" => $participants,
            "data" => $data,
        ]);
    }

    #[Route('/tableau/formulaireMiseAJourTableau', name: 'afficherFormulaireMiseAJourTableau')]
    public static function afficherFormulaireMiseAJourTableau(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            (new ControleurTableau)->redirection("utilisateur", ["action" => "afficherFormulaireConnexion"]);
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant du tableau manquant");
            (new ControleurTableau)->redirection("base", ["action" => "../accueil"]);
        }
        $repository = new TableauRepository();
        /**
         * @var Tableau $tableau
         */
        $tableau = $repository->recupererParClePrimaire($_REQUEST["idTableau"]);
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            (new ControleurTableau)->redirection("base", ["action" => "../accueil"]);
        }
        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            (new ControleurTableau)->redirection("tableau",  ["action"=>"tableau"]);
        }
       return ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Modification d'un tableau",
            "cheminVueBody" => "tableau/formulaireMiseAJourTableau.php",
            "idTableau" => $_REQUEST["idTableau"],
            "nomTableau" => $tableau->getTitreTableau()
        ]);
    }

    #[Route('/tableau/formulaireCreationTableau', name: 'afficherFormulaireCreationTableau')]
    public static function afficherFormulaireCreationTableau(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            (new ControleurTableau)->redirection("utilisateur", ["action" => "afficherFormulaireConnexion"]);
        }
      return ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Ajout d'un tableau",
            "cheminVueBody" => "tableau/formulaireCreationTableau.php",
        ]);
    }

    #[Route('/tableau/creerTableau', name: 'creerTableau')]
    public static function creerTableau(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
           return (new ControleurTableau)->redirection("utilisateur", ["action" => "afficherFormulaireConnexion"]);
        }
        $utilisateurRepository = new UtilisateurRepository();

        /**
         * @var Utilisateur $utilisateur
         */
        $utilisateur = $utilisateurRepository->recupererParClePrimaire(ConnexionUtilisateur::getLoginUtilisateurConnecte());
        if(!ControleurCarte::issetAndNotNull(["nomTableau"])) {
            MessageFlash::ajouter("danger", "Nom de tableau manquant");
          return  (new ControleurTableau)->redirection("tableau", ["action" => "afficherFormulaireCreationTableau"]);
        }
        $tableauRepository = new TableauRepository();
        $idTableau = $tableauRepository->getNextIdTableau();
        $codeTableau = hash("sha256", $utilisateur->getLogin().$idTableau);

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

      return  (new ControleurTableau)->redirection("tableau", ["codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route('/tableau/mettreAJourTableau', name: 'mettreAJourTableau')]
    public static function mettreAJourTableau(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
          return  (new ControleurTableau)->redirection("utilisateur", ["action" => "afficherFormulaireConnexion"]);
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant de tableau manquant");
          return  (new ControleurTableau)->redirection("base", ["action" => "../accueil"]);
        }
        $repository = new TableauRepository();

        /**
         * @var Tableau $tableau
         */
        $tableau = $repository->recupererParClePrimaire($_REQUEST["idTableau"]);
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
          return  (new ControleurTableau)->redirection("base",  ["action" => "../accueil"]);
        }
        if(!ControleurCarte::issetAndNotNull(["nomTableau"])) {
            MessageFlash::ajouter("danger", "Nom de tableau manquant");
           return (new ControleurTableau)->redirection("tableau", ["action"=>"afficherFormulaireMiseAJourTableau", "idTableau" => $_REQUEST["idTableau"]]);
        }
        if(!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
        }
        else {
            $tableau->setTitreTableau($_REQUEST["nomTableau"]);
            $repository->mettreAJour($tableau);
        }
       return (new ControleurTableau)->redirection("tableau", [ "codeTableau" => $tableau->getCodeTableau()]);
    }

    #[Route('/tableau/formulaireAjoutMembre', name: 'afficherFormulaireAjoutMembre')]
    public static function afficherFormulaireAjoutMembre(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
            (new ControleurTableau)->redirection("utilisateur", ["action" => "afficherFormulaireConnexion"]);
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant du tableau manquant");
            (new ControleurTableau)->redirection("base", ["action" => "../accueil"]);
        }
        $repository = new TableauRepository();
        /**
         * @var Tableau $tableau
         */
        $tableau = $repository->recupererParClePrimaire($_REQUEST["idTableau"]);
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            (new ControleurTableau)->redirection("base", ["action" => "../accueil"]);
        }
        if(!$tableau->estProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'êtes pas propriétaire de ce tableau");
            (new ControleurTableau)->redirection("tableau", [ "codeTableau" => $tableau->getCodeTableau()]);
        }

        $utilisateurRepository = new UtilisateurRepository();

        /**
         * @var Utilisateur[] $utilisateurs
         */
        $utilisateurs = $utilisateurRepository->recupererUtilisateursOrderedPrenomNom();
        $filtredUtilisateurs = array_filter($utilisateurs, function ($u) use ($tableau) {return !$tableau->estParticipantOuProprietaire($u->getLogin());});

        if(empty($filtredUtilisateurs)) {
            MessageFlash::ajouter("warning", "Il n'est pas possible d'ajouter plus de membre à ce tableau.");
            (new ControleurTableau)->redirection("tableau", [ "codeTableau" => $tableau->getCodeTableau()]);
        }

       return ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Ajout d'un membre",
            "cheminVueBody" => "tableau/formulaireAjoutMembreTableau.php",
            "tableau" => $tableau,
            "utilisateurs" => $filtredUtilisateurs
        ]);
    }

    #[Route('/tableau/ajouterMembre', name: 'ajouterMembreTableau')]
    public static function ajouterMembre(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
         return   (new ControleurTableau)->redirection("utilisateur", ["action" => "afficherFormulaireConnexion"]);
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant du tableau manquant");
          return  (new ControleurTableau)->redirection("base", ["action" => "../accueil"]);
        }
        $repository = new TableauRepository();

        /**
         * @var Tableau $tableau
         */
        $tableau = $repository->recupererParClePrimaire($_REQUEST["idTableau"]);
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
          return  (new ControleurTableau)->redirection("base", ["action" => "../accueil"]);
        }
        if(!$tableau->estProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'êtes pas propriétaire de ce tableau");
          return  (new ControleurTableau)->redirection("tableau", [ "codeTableau" => $tableau->getCodeTableau()]);
        }
        if(!ControleurCarte::issetAndNotNull(["login"])) {
            MessageFlash::ajouter("danger", "Login du membre à ajouter manquant");
          return  (new ControleurTableau)->redirection("tableau", [ "codeTableau" => $tableau->getCodeTableau()]);
        }

        $utilisateurRepository = new UtilisateurRepository();
        /**
         * @var Utilisateur $utilisateur
         */
        $utilisateur = $utilisateurRepository->recupererParClePrimaire($_REQUEST["login"]);
        if(!$utilisateur) {
            MessageFlash::ajouter("danger", "Utlisateur inexistant");
          return  (new ControleurTableau)->redirection("tableau", [ "codeTableau" => $tableau->getCodeTableau()]);
        }
        if($tableau->estParticipantOuProprietaire($utilisateur->getLogin())) {
            MessageFlash::ajouter("warning", "Ce membre est déjà membre du tableau.");
            $arguments = array_merge([ "codeTableau" => $tableau->getCodeTableau()], $_REQUEST);
          return  (new ControleurTableau)->redirection("tableau", $arguments);
        }

        $participants = $tableau->getParticipants();
        $participants[] = $utilisateur;
        $tableau->setParticipants($participants);
        $repository->mettreAJour($tableau);

        $arguments = array_merge(["codeTableau" => $tableau->getCodeTableau()], $_REQUEST);
        return  (new ControleurTableau)->redirection("tableau", $arguments);
    }

    #[Route('/tableau/supprimerMembre', name: 'supprimerMembre')]
    public static function supprimerMembre(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
           return (new ControleurTableau)->redirection("utilisateur", ["action" => "afficherFormulaireConnexion"]);
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant du tableau manquant");
          return  (new ControleurTableau)->redirection("base", ["action" => "../accueil"]);
        }
        $repository = new TableauRepository();
        /**
         * @var Tableau $tableau
         */
        $tableau = $repository->recupererParClePrimaire($_REQUEST["idTableau"]);
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
          return  (new ControleurTableau)->redirection("base", ["action" => "../accueil"]);
        }
        if(!$tableau->estProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'êtes pas propriétaire de ce tableau");
            $arguments = array_merge([ "codeTableau" => $tableau->getCodeTableau()], $_REQUEST);
            return  (new ControleurTableau)->redirection("tableau", $arguments);        }
        if(!ControleurCarte::issetAndNotNull(["login"])) {
            MessageFlash::ajouter("danger", "Login du membre à supprimer manquant");
            $arguments = array_merge([ "codeTableau" => $tableau->getCodeTableau()], $_REQUEST);
            return  (new ControleurTableau)->redirection("tableau", $arguments);        }
        $utilisateurRepository = new UtilisateurRepository();
        /**
         * @var Utilisateur $utilisateur
         */
        $utilisateur = $utilisateurRepository->recupererParClePrimaire($_REQUEST["login"]);
        if(!$utilisateur) {
            MessageFlash::ajouter("danger", "Utlisateur inexistant");
            $arguments = array_merge([ "codeTableau" => $tableau->getCodeTableau()], $_REQUEST);
            return  (new ControleurTableau)->redirection("tableau", $arguments);        }
        if($tableau->estProprietaire($utilisateur->getLogin())) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas vous supprimer du tableau.");
            $arguments = array_merge(["action" => "tableau", "codeTableau" => $tableau->getCodeTableau()], $_REQUEST);
            return  (new ControleurTableau)->redirection("tableau", $arguments);        }
        if(!$tableau->estParticipant($utilisateur->getLogin())) {
            MessageFlash::ajouter("danger", "Cet utilisateur n'est pas membre du tableau");
            $arguments = array_merge([ "codeTableau" => $tableau->getCodeTableau()], $_REQUEST);
            return  (new ControleurTableau)->redirection("tableau", $arguments);        }

        $participants = array_filter($tableau->getParticipants(), function ($u) use ($utilisateur) {return $u->getLogin() !== $utilisateur->getLogin();});
        $tableau->setParticipants($participants);
        $repository->mettreAJour($tableau);

        $cartesRepository = new CarteRepository();
        $cartes = $cartesRepository->recupererCartesTableau($tableau->getIdTableau());
        foreach ($cartes as $carte) {
            $affectations = array_filter($carte->getAffectationsCarte(), function ($u) use ($utilisateur) {return $u->getLogin() != $utilisateur->getLogin();});
            $carte->setAffectationsCarte($affectations);
            $cartesRepository->mettreAJour($carte);
        }
        $arguments = array_merge(["codeTableau" => $tableau->getCodeTableau()], $_REQUEST);
        return  (new ControleurTableau)->redirection("tableau", $arguments);    }

    #[Route('/tableau/listeTableauxUtilisateur', name: 'afficherListeMesTableaux')]
    public static function afficherListeMesTableaux() : Response {
        if(!ConnexionUtilisateur::estConnecte()) {
         return  (new ControleurTableau)->redirection("utilisateur", ["action" => "listeTableauxUtilisateur"]);
        }
        $repository = new TableauRepository();
        $login = ConnexionUtilisateur::getLoginUtilisateurConnecte();
        $tableaux = $repository->recupererTableauxOuUtilisateurEstMembre($login);
       return ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Liste des tableaux de $login",
            "cheminVueBody" => "tableau/listeTableauxUtilisateur.php",
            "tableaux" => $tableaux
        ]);
    }

    #[Route('/tableau/quitterTableau', name: 'quitterTableau')]
    public static function quitterTableau(): Response {
        if(!ConnexionUtilisateur::estConnecte()) {
          return  (new ControleurTableau)->redirection("utilisateur", ["action" => "afficherFormulaireConnexion"]);
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant du tableau manquant");
         return   (new ControleurTableau)->redirection("tableau", ["action" => "afficherListeMesTableaux"]);
        }
        $repository = new TableauRepository();
        /**
         * @var Tableau $tableau
         */
        $tableau = $repository->recupererParClePrimaire($_REQUEST["idTableau"]);
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
           return (new ControleurTableau)->redirection("tableau", ["action" => "afficherListeMesTableaux"]);
        }

        $utilisateurRepository = new UtilisateurRepository();

        /**
         * @var Utilisateur $utilisateur
         */
        $utilisateur = $utilisateurRepository->recupererParClePrimaire(ConnexionUtilisateur::getLoginUtilisateurConnecte());
        if($tableau->estProprietaire($utilisateur->getLogin())) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas quitter ce tableau");
           return (new ControleurTableau)->redirection("tableau", ["action" => "afficherListeMesTableaux"]);
        }
        if(!$tableau->estParticipant(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'appartenez pas à ce tableau");
         return   (new ControleurTableau)->redirection("tableau", ["action" => "afficherListeMesTableaux"]);
        }
        $participants = array_filter($tableau->getParticipants(), function ($u) use ($utilisateur) {return $u->getLogin() !== $utilisateur->getLogin();});
        $tableau->setParticipants($participants);
        $repository->mettreAJour($tableau);

        $carteRepository = new CarteRepository();

        /**
         * @var Carte[] $cartes
         */
        $cartes = $carteRepository->recupererCartesTableau($tableau->getIdTableau());
        foreach ($cartes as $carte) {
            $affectations = array_filter($carte->getAffectationsCarte(), function ($u) use ($utilisateur) {return $u->getLogin() != $utilisateur->getLogin();});
            $carte->setAffectationsCarte($affectations);
            $carteRepository->mettreAJour($carte);
        }
       return (new ControleurTableau)->redirection("tableau", ["action" => "afficherListeMesTableaux"]);
    }

    public static function supprimerTableau(): void {
        if(!ConnexionUtilisateur::estConnecte()) {
            (new ControleurTableau)->redirection("utilisateur", ["action" => "afficherFormulaireConnexion"]);
        }
        if(!ControleurCarte::issetAndNotNull(["idTableau"])) {
            MessageFlash::ajouter("danger", "Identifiant de tableau manquant");
            (new ControleurTableau)->redirection("tableau", ["action" => "afficherListeMesTableaux"]);
        }
        $repository = new TableauRepository();
        $idTableau = $_REQUEST["idTableau"];
        /**
         * @var Tableau $tableau
         */
        $tableau = $repository->recupererParClePrimaire($idTableau);
        if(!$tableau) {
            MessageFlash::ajouter("danger", "Tableau inexistant");
            (new ControleurTableau)->redirection("tableau", ["action" => "afficherListeMesTableaux"]);
        }
        if(!$tableau->estProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            MessageFlash::ajouter("danger", "Vous n'êtes pas propriétaire de ce tableau");
            (new ControleurTableau)->redirection("tableau", ["action" => "afficherListeMesTableaux"]);
        }
        if($repository->getNombreTableauxTotalUtilisateur(ConnexionUtilisateur::getLoginUtilisateurConnecte()) == 1) {
            MessageFlash::ajouter("danger", "Vous ne pouvez pas supprimer ce tableau car cela entrainera la supression du compte");
            (new ControleurTableau)->redirection("tableau", ["action" => "afficherListeMesTableaux"]);
        }
        $repository->supprimer($idTableau);
        (new ControleurTableau)->redirection("tableau", ["action" => "afficherListeMesTableaux"]);
    }
}