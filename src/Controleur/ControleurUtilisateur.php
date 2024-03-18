<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Lib\MotDePasse;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\HTTP\Cookie;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurUtilisateur extends ControleurGenerique
{

public function __construct()
{

}


    public static function afficherErreur($messageErreur = "", $controleur = ""): Response
    {
       return parent::afficherErreur($messageErreur, "utilisateur");
    }

    #[Route('utilisateur/detail', name: 'afficherDetails')]
    public static function afficherDetail(): Response
    {
        if(!ConnexionUtilisateur::estConnecte()) {
            (new ControleurTableau)->redirection("utilisateur", ["action" => "../utilisateur/formulaireConnexion"]);
        }
        $utilisateur = (new UtilisateurRepository())->recupererParClePrimaire(ConnexionUtilisateur::getLoginUtilisateurConnecte());
       return ControleurUtilisateur::afficherVue('vueGenerale.php', [
            "utilisateur" => $utilisateur,
            "pagetitle" => "Détail de l'utilisateur {$utilisateur->getLogin()}",
            "cheminVueBody" => "utilisateur/detail.php"
        ]);
    }

    #[Route('utilisateur/formulaireCreation', name: 'afficherFormulaireInscription')]
    public static function afficherFormulaireCreation(): Response
    {
        if(ConnexionUtilisateur::estConnecte()) {
            (new ControleurTableau)->redirection("tableau", ["action" => "afficherListeMesTableaux"]);
        }
       return ControleurUtilisateur::afficherVue('vueGenerale.php', [
            "pagetitle" => "Création d'un utilisateur",
            "cheminVueBody" => "utilisateur/formulaireCreation.php"
        ]);
    }

    #[Route('utilisateur/creation', name: 'creerDepuisFormulaire')]
    public static function creerDepuisFormulaire(): Response
    {
        if(ConnexionUtilisateur::estConnecte()) {
         return   (new ControleurTableau)->redirection("tableau", ["action" => "afficherListeMesTableaux"]);
        }
        if (ControleurUtilisateur::issetAndNotNull(["login", "prenom", "nom", "mdp", "mdp2", "email"])) {
            if ($_REQUEST["mdp"] !== $_REQUEST["mdp2"]) {
                MessageFlash::ajouter("warning", "Mots de passe distincts.");
            return     (new ControleurUtilisateur)->redirection("utilisateur", ["action" => "afficherFormulaireCreation"]);
            }

            if (!filter_var($_REQUEST["email"], FILTER_VALIDATE_EMAIL)) {
                MessageFlash::ajouter("warning", "Email non valide");
             return   (new ControleurUtilisateur)->redirection("utilisateur", ["action" => "afficherFormulaireCreation"]);
            }

            $utilisateurRepository = new UtilisateurRepository();

            $checkUtilisateur = $utilisateurRepository->recupererParClePrimaire($_REQUEST["login"]);
            if($checkUtilisateur) {
                MessageFlash::ajouter("warning", "Le login est déjà pris.");
            return    (new ControleurUtilisateur)->redirection("utilisateur", ["action" => "afficherFormulaireCreation"]);
            }

            $tableauRepository = new TableauRepository();
            $colonneRepository = new ColonneRepository();
            $carteRepository = new CarteRepository();

            $mdpHache = MotDePasse::hacher($_REQUEST["mdp"]);
            $idTableau = $tableauRepository->getNextIdTableau();
            $codeTableau = hash("sha256", $_REQUEST["login"].$idTableau);
            $tableauInitial = "Mon tableau";

            $idColonne1 = $colonneRepository->getNextIdColonne();
            $colonne1 = "TODO";

            $colonne2 = "DOING";
            $idColonne2 = $idColonne1 + 1;

            $colonne3 = "DONE";
            $idColonne3 = $idColonne1 + 2;

            $carteInitiale = "Exemple";
            $descriptifInitial = "Exemple de carte";
            $idCarte1 = $carteRepository->getNextIdCarte();
            $idCarte2 = $idCarte1 + 1;
            $idCarte3 = $idCarte1 + 2;

            $tableau = new Tableau(
                new Utilisateur(
                    $_REQUEST["login"],
                    $_REQUEST["nom"],
                    $_REQUEST["prenom"],
                    $_REQUEST["email"],
                    $mdpHache,
                    $_REQUEST["mdp"],
                ),
                $idTableau,
                $codeTableau,
                $tableauInitial,
                [],
            );

            $carte1 = new Carte(
                new Colonne(
                    $tableau,
                    $idColonne1,
                    $colonne1,
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
                    $colonne2,
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
                    $colonne3,
                ),
                $idCarte3,
                $carteInitiale,
                $descriptifInitial,
                "#FFFFFF",
                []
            );

            $succesSauvegarde = $carteRepository->ajouter($carte1) && $carteRepository->ajouter($carte2) && $carteRepository->ajouter($carte3);
            if ($succesSauvegarde) {
                Cookie::enregistrer("login", $_REQUEST["login"]);
                Cookie::enregistrer("mdp", $_REQUEST["mdp"]);
                MessageFlash::ajouter("success", "L'utilisateur a bien été créé !");
             return   (new ControleurUtilisateur)->redirection("utilisateur", ["action" => "../utilisateur/formulaireConnexion"]);
            }
            else {
                MessageFlash::ajouter("warning", "Une erreur est survenue lors de la création de l'utilisateur.");
             return   (new ControleurUtilisateur)->redirection("utilisateur", ["action" => "afficherFormulaireCreation"]);
            }
        } else {
            MessageFlash::ajouter("danger", "Login, nom, prenom, email ou mot de passe manquant.");
          return  (new ControleurUtilisateur)->redirection("utilisateur", ["action" => "afficherFormulaireCreation"]);
        }
    }

    #[Route('utilisateur/formulaireMiseAJour', name: 'afficherFormulaireMiseAJourUtilisateur')]
    public static function afficherFormulaireMiseAJour(): Response
    {
        if(!ConnexionUtilisateur::estConnecte()) {
            (new ControleurTableau)->redirection("utilisateur", ["action" => "../utilisateur/formulaireConnexion"]);
        }
        $login = ConnexionUtilisateur::getLoginUtilisateurConnecte();
        $repository = new UtilisateurRepository();
        $utilisateur = $repository->recupererParClePrimaire($login);
       return ControleurUtilisateur::afficherVue('vueGenerale.php', [
            "pagetitle" => "Mise à jour du profil",
            "cheminVueBody" => "utilisateur/formulaireMiseAJour.php",
            "utilisateur" => $utilisateur,
        ]);
    }

    #[Route('utilisateur/miseAJour', name: 'mettreAJour')]
    public static function mettreAJour(): void
    {
        if(!ConnexionUtilisateur::estConnecte()) {
            (new ControleurTableau)->redirection("utilisateur", ["action" => "../utilisateur/formulaireConnexion"]);
        }
        if (ControleurUtilisateur::issetAndNotNull(["login", "prenom", "nom", "mdp", "mdp2", "email"])) {
            $login = $_REQUEST['login'];
            $repository = new UtilisateurRepository();

            /**
             * @var Utilisateur $utilisateur
             */
            $utilisateur = $repository->recupererParClePrimaire($login);

            if(!$utilisateur) {
                MessageFlash::ajouter("danger", "L'utilisateur n'existe pas");
                (new ControleurUtilisateur)->redirection("utilisateur", ["action" => "afficherFormulaireMiseAJour"]);
            }

            if (!filter_var($_REQUEST["email"], FILTER_VALIDATE_EMAIL)) {
                MessageFlash::ajouter("warning", "Email non valide");
                (new ControleurUtilisateur)->redirection("utilisateur", ["action" => "afficherFormulaireMiseAJour"]);
            }

            if (!(MotDePasse::verifier($_REQUEST["mdpAncien"], $utilisateur->getMdpHache()))) {
                MessageFlash::ajouter("warning", "Ancien mot de passe erroné.");
                (new ControleurUtilisateur)->redirection("utilisateur", ["action" => "afficherFormulaireMiseAJour"]);
            }

            if ($_REQUEST["mdp"] !== $_REQUEST["mdp2"]) {
                MessageFlash::ajouter("warning", "Mots de passe distincts.");
                (new ControleurUtilisateur)->redirection("utilisateur",  ["action" => "afficherFormulaireMiseAJour"]);
            }

            $utilisateur->setNom($_REQUEST["nom"]);
            $utilisateur->setPrenom($_REQUEST["prenom"]);
            $utilisateur->setEmail($_REQUEST["email"]);
            $utilisateur->setMdpHache(MotDePasse::hacher($_REQUEST["mdp"]));
            $utilisateur->setMdp($_REQUEST["mdp"]);

            $repository->mettreAJour($utilisateur);

            $carteRepository = new CarteRepository();
            $cartes = $carteRepository->recupererCartesUtilisateur($login);
            foreach ($cartes as $carte) {
                $participants = $carte->getAffectationsCarte();
                $participants = array_filter($participants, function ($u) use ($login) {return $u->getLogin() !== $login;});
                $participants[] = $utilisateur;
                $carte->setAffectationsCarte($participants);
                $carteRepository->mettreAJour($carte);
            }

            $tableauRepository = new TableauRepository();
            $tableaux = $tableauRepository->recupererTableauxParticipeUtilisateur($login);
            foreach ($tableaux as $tableau) {
                $participants = $tableau->getParticipants();
                $participants = array_filter($participants, function ($u) use ($login) {return $u->getLogin() !== $login;});
                $participants[] = $utilisateur;
                $tableau->setParticipants($participants);
                $tableauRepository->mettreAJour($tableau);
            }

            Cookie::enregistrer("mdp", $_REQUEST["mdp"]);

            MessageFlash::ajouter("success", "L'utilisateur a bien été modifié !");
            (new ControleurUtilisateur)->redirection("tableau", ["action" => "afficherListeMesTableaux"]);
        } else {
            MessageFlash::ajouter("danger", "Login, nom, prenom, email ou mot de passe manquant.");
            (new ControleurUtilisateur)->redirection("utilisateur", ["action" => "afficherFormulaireMiseAJour"]);
        }
    }

    #[Route('utilisateur/suppression', name: 'supprimer')]
    public static function supprimer(): void
    {
        if(!ConnexionUtilisateur::estConnecte()) {
            (new ControleurTableau)->redirection("utilisateur", ["action" => "../utilisateur/formulaireConnexion"]);
        }
        if (!ControleurUtilisateur::issetAndNotNull(["login"])) {
            MessageFlash::ajouter("warning", "Login manquant");
            (new ControleurUtilisateur)->redirection("utilisateur", ["action" => "afficherDetail"]);
        }
        $login = $_REQUEST["login"];

        $carteRepository = new CarteRepository();
        $cartes = $carteRepository->recupererCartesUtilisateur($login);
        foreach ($cartes as $carte) {
            $participants = $carte->getAffectationsCarte();
            $participants = array_filter($participants, function ($u) use ($login) {return $u->getLogin() !== $login;});
            $carte->setAffectationsCarte($participants);
            $carteRepository->mettreAJour($carte);
        }

        $tableauRepository = new TableauRepository();
        $tableaux = $tableauRepository->recupererTableauxParticipeUtilisateur($login);
        foreach ($tableaux as $tableau) {
            $participants = $tableau->getParticipants();
            $participants = array_filter($participants, function ($u) use ($login) {return $u->getLogin() !== $login;});
            $tableau->setParticipants($participants);
            $tableauRepository->mettreAJour($tableau);
        }
        $repository = new UtilisateurRepository();
        $repository->supprimer($login);
        Cookie::supprimer("login");
        Cookie::supprimer("mdp");
        ConnexionUtilisateur::deconnecter();
        MessageFlash::ajouter("success", "Votre compte a bien été supprimé !");
        (new ControleurUtilisateur)->redirection("utilisateur", ["action" => "../utilisateur/formulaireConnexion"]);
    }

    #[Route('utilisateur/formulaireConnexion', name: 'afficherFormulaireConnexion')]
    public static function afficherFormulaireConnexion(): Response
    {
        if(ConnexionUtilisateur::estConnecte()) {
            (new ControleurTableau)->redirection("tableau", ["action" => "../tableau/listeTableauxUtilisateur"]);
        }
      return  ControleurUtilisateur::afficherVue('vueGenerale.php', [
            "pagetitle" => "Formulaire de connexion",
            "cheminVueBody" => "utilisateur/formulaireConnexion.php"
        ]);
    }

    #[Route('utilisateur/connexion', name: 'connecter')]
    public static function connecter(): Response
    {
        if(ConnexionUtilisateur::estConnecte()) {
          return  (new ControleurTableau)->redirection("tableau", ["action" => "../tableau/listeTableauxUtilisateur"]);
        }
        if (!ControleurUtilisateur::issetAndNotNull(["login", "mdp"])) {
            MessageFlash::ajouter("warning", "Login ou mot de passe manquant");
         return   (new ControleurUtilisateur)->redirection("utilisateur", ["action" => "../utilisateur/formulaireConnexion"]);
        }
        $repository = new UtilisateurRepository();
        $utilisateur = $repository->recupererParClePrimaire($_REQUEST["login"]);
        if (!$utilisateur) {
            MessageFlash::ajouter("warning", "Utilisateur inconnu");
           return (new ControleurUtilisateur)->redirection("utilisateur", ["action" => "../utilisateur/formulaireConnexion"]);
        }
        if (!MotDePasse::verifier($_REQUEST["mdp"], $utilisateur->getMdpHache())) {
            MessageFlash::ajouter("warning", "Mot de passe incorrect");
          return  (new ControleurUtilisateur)->redirection("utilisateur", ["action" => "../utilisateur/formulaireConnexion"]);
        }
        ConnexionUtilisateur::connecter($utilisateur->getLogin());
        MessageFlash::ajouter("success", "Connexion effectuée.");
       return (new ControleurTableau)->redirection("tableau", ["action" => "../tableau/listeTableauxUtilisateur"]);
    }


    #[Route('utilisateur/deconnexion', name: 'deconnexion')]
    public static function deconnecter(): Response
    {
        if (!ConnexionUtilisateur::estConnecte()) {
            MessageFlash::ajouter("danger", "Utilisateur non connecté.");
          return  (new ControleurUtilisateur)->redirection("base", ["action" => "../accueil"]);
        }
        ConnexionUtilisateur::deconnecter();
        MessageFlash::ajouter("success", "L'utilisateur a bien été déconnecté.");
       return (new ControleurUtilisateur)->redirection("base", ["action" => "../accueil"]);
    }

    #[Route('utilisateur/resetCompte', name: 'utilisateurResetCompte')]
    public static function afficherFormulaireRecuperationCompte(): Response {
        if(ConnexionUtilisateur::estConnecte()) {
            (new ControleurTableau)->redirection("tableau", ["action" => "afficherListeMesTableaux"]);
        }
      return ControleurUtilisateur::afficherVue('vueGenerale.php', [
            "pagetitle" => "Récupérer mon compte",
            "cheminVueBody" => "utilisateur/resetCompte.php"
        ]);
    }

    #[Route('utilisateur/resultatResetCompte', name: 'recupererCompte')]
    public static function recupererCompte(): Response {
        if(ConnexionUtilisateur::estConnecte()) {
         return   (new ControleurTableau)->redirection("tableau", ["action" => "afficherListeMesTableaux"]);
        }
        if (!ControleurUtilisateur::issetAndNotNull(["email"])) {
            MessageFlash::ajouter("warning", "Adresse email manquante");
          return  (new ControleurUtilisateur)->redirection("utilisateur", ["action" => "../utilisateur/formulaireConnexion"]);
        }
        $repository = new UtilisateurRepository();
        $utilisateurs = $repository->recupererUtilisateursParEmail($_REQUEST["email"]);
        if(empty($utilisateurs)) {
            MessageFlash::ajouter("warning", "Aucun compte associé à cette adresse email");
          return  (new ControleurUtilisateur)->redirection("utilisateur", ["action" => "../utilisateur/formulaireConnexion"]);
        }
       return ControleurUtilisateur::afficherVue('vueGenerale.php', [
            "pagetitle" => "Récupérer mon compte",
            "cheminVueBody" => "utilisateur/resultatResetCompte.php",
            "utilisateurs" => $utilisateurs
        ]);
    }
}