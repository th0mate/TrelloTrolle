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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ControleurTableau extends ControleurGenerique
{

    public function __construct(ContainerInterface         $container,
                                private ServiceTableau     $serviceTableau,
                                private ServiceConnexion   $serviceConnexion,
                                private ServiceUtilisateur $serviceUtilisateur,
                                private ServiceCarte       $serviceCarte)
    {
        parent::__construct($container);

    }

    public function afficherErreur($messageErreur = "", $controleur = ""): Response
    {
        return parent::afficherErreur($messageErreur, "tableau");
    }

    #[Route('/tableau/{codeTableau}', name: 'afficherTableau', methods: "GET")]
    public function afficherTableau($codeTableau): Response
    {

        //$codeTableau = $_REQUEST["codeTableau"] ?? null;
        try {
            $tableau = $this->serviceTableau->recupererTableauParCode($codeTableau);
            $donnes = $this->serviceTableau->recupererCartesColonnes($tableau);

            /*return ControleurTableau::afficherVue('vueGenerale.php', [
                "pagetitle" => "{$tableau->getTitreTableau()}",
                "cheminVueBody" => "tableau/tableau.php",
                "tableau" => $tableau,
                "colonnes" => $donnes["colonnes"],
                "participants" => $donnes["participants"],
                "data" => $donnes["data"],
            ]);*/
            return $this->afficherTwig('tableau/tableau.html.twig',["tableau" => $tableau,
                "colonnes" => $donnes["colonnes"],
                "participants" => $donnes["participants"],
                "data" => $donnes["data"]]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection("base", 'accueil');
        }


    }

    #[Route('/tableau/mettreAJour', name: 'afficherFormulaireMiseAJourTableau', methods: "GET")]
    public function afficherFormulaireMiseAJourTableau(): Response
    {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $tableau = $this->serviceTableau->recupererTableauParId($idTableau);
            $this->serviceUtilisateur->estParticipant($tableau);
            /*return ControleurTableau::afficherVue('vueGenerale.php', [
                "pagetitle" => "Modification d'un tableau",
                "cheminVueBody" => "tableau/formulaireMiseAJourTableau.php",
                "idTableau" => $_REQUEST["idTableau"],
                "nomTableau" => $tableau->getTitreTableau()
            ]);*/
            return $this->afficherTwig('tableau/formulaireMiseAJourTableau.html.twig',[
                "nomTableau" => $tableau->getTitreTableau()
            ]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("base", "accueil");
        }
    }

    #[Route('/tableau/nouveau', name: 'afficherFormulaireCreationTableau', methods: "GET")]
    public function afficherFormulaireCreationTableau(): Response
    {
        try {
            $this->serviceConnexion->pasConnecter();
            /*return ControleurTableau::afficherVue('vueGenerale.php', [
                "pagetitle" => "Ajout d'un tableau",
                "cheminVueBody" => "tableau/formulaireCreationTableau.php",
            ]);*/
            return $this->afficherTwig('tableau/formulaireCreationTableau.html.twig');
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        }
    }

    #[Route('/tableau/nouveau', name: 'creerTableau', methods: "POST")]
    public function creerTableau(): Response
    {
        $nomTableau = $_REQUEST["nomTableau"] ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $tableau = $this->serviceTableau->creerTableau($nomTableau);
            return ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);

        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("tableau", "afficherFormulaireCreationTableau");
        }
    }

    #[Route('/tableau/mettreAJour', name: 'mettreAJourTableau', methods: "POST")]
    public function mettreAJourTableau(): Response
    {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        $nomTableau = $_REQUEST["nomTableau"] ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $tableau = $this->serviceTableau->recupererTableauParId($idTableau);
            $this->serviceTableau->isNotNullNomTableau($nomTableau, $tableau);
            if (!$tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
                MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            } else {
                $tableau->setTitreTableau($_REQUEST["nomTableau"]);
                $this->serviceTableau->mettreAJourTableau($tableau);
            }
            return ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurTableau::redirection("tableau", "afficherFormulaireMiseAJourTableau", ["idTableau" => $_REQUEST["idTableau"]]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("base", "accueil");
        }
    }

    #[Route('/tableau/inviter', name: 'afficherFormulaireAjoutMembre', methods: "GET")]
    public function afficherFormulaireAjoutMembre(): Response
    {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $tableau = $this->serviceTableau->recupererTableauParId($idTableau);
            $filtredUtilisateurs = $this->serviceUtilisateur->verificationsMembre($tableau, ConnexionUtilisateur::getLoginUtilisateurConnecte());
            /*return ControleurTableau::afficherVue('vueGenerale.php', [
                "pagetitle" => "Ajout d'un membre",
                "cheminVueBody" => "tableau/formulaireAjoutMembreTableau.php",
                "tableau" => $tableau,
                "utilisateurs" => $filtredUtilisateurs
            ]);*/
            return $this->afficherTwig('tableau/formulaireAjoutMembreTableau.html.twig',[
                "utilisateurs" => $filtredUtilisateurs
            ]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("base", "accueil");
        }
    }

    #[Route('/tableau/inviter', name: 'ajouterMembreTableau', methods: "POST")]
    public function ajouterMembre(): Response
    {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        $login = $_REQUEST["login"] ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $tableau = $this->serviceTableau->recupererTableauParId($idTableau);
            $this->serviceUtilisateur->ajouterMembre($tableau, $login);
            return ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
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

    #[Route('/tableau/supprimerMembre', name: 'supprimerMembre', methods: "GET")]
    public function supprimerMembre(): Response
    {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        $login = $_REQUEST["login"] ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $tableau = $this->serviceTableau->recupererTableauParId($idTableau);
            $utilisateur = $this->serviceUtilisateur->supprimerMembre($tableau, $login);
            $this->serviceCarte->miseAJourCarteMembre($tableau, $utilisateur);
            return ControleurTableau::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);

        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("tableau", "afficherTableau", ['codeTableau' => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("base", "accueil");
        }
    }

    #[Route('/tableau', name: 'afficherListeMesTableaux', methods: "GET")]
    public function afficherListeMesTableaux(): Response
    {
        try {
            $this->serviceConnexion->pasConnecter();

            $tableaux = $this->serviceTableau->recupererTableauEstMembre(ConnexionUtilisateur::getLoginUtilisateurConnecte());
            /*return ControleurTableau::afficherVue('vueGenerale.php', [
                "pagetitle" => "Liste des tableaux de $login",
                "cheminVueBody" => "tableau/listeTableauxUtilisateur.php",
                "tableaux" => $tableaux
            ]);*/
            return $this->afficherTwig('tableau/listeTableauxUtilisateur.html.twig',["tableaux" => $tableaux]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        }
    }

    #[Route('/tableau/quitter', name: 'quitterTableau', methods: "GET")]
    public function quitterTableau($idTableau): Response
    {
        //$idTableau = $_REQUEST["idTableau"] ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $tableau = $this->serviceTableau->recupererTableauParId($idTableau);
            $utilisateur = $this->serviceUtilisateur->recupererUtilisateurParCle(ConnexionUtilisateur::getLoginUtilisateurConnecte());
            $this->serviceTableau->quitterTableau($tableau, $utilisateur);
            return ControleurTableau::redirection("tableau", "afficherListeMesTableaux");

        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("tableau", "afficherListeMesTableaux");
        }
    }

    #[Route('/{idTableau}/suppression', name: 'supprimerTableau', methods: "GET")]
    public function supprimerTableau($idTableau): Response
    {
        try {
            $this->serviceConnexion->pasConnecter();
            $tableau = $this->serviceTableau->recupererTableauParId($idTableau);
            $this->serviceUtilisateur->estProprietaire($tableau, ConnexionUtilisateur::getLoginUtilisateurConnecte());
            $this->serviceTableau->supprimerTableau($idTableau);
            return ControleurTableau::redirection("tableau", "afficherListeMesTableaux");
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("tableau", "afficherListeMesTableaux");
        }
    }
}