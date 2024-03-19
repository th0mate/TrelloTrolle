<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Service\Exception\ConnexionException;
use App\Trellotrolle\Service\Exception\CreationCarteException;
use App\Trellotrolle\Service\Exception\MiseAJourException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;
use App\Trellotrolle\Service\ServiceCarte;
use App\Trellotrolle\Service\ServiceColonne;
use App\Trellotrolle\Service\ServiceConnexion;
use App\Trellotrolle\Service\ServiceUtilisateur;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class ControleurCarte extends ControleurGenerique
{

    public function __construct(ContainerInterface         $container,
                                private ServiceConnexion   $serviceConnexion,
                                private ServiceCarte       $serviceCarte,
                                private ServiceUtilisateur $serviceUtilisateur,
                                private ServiceColonne     $serviceColonne)
    {
        parent::__construct($container);
    }

    public function afficherErreur($messageErreur = "", $controleur = ""): Response
    {
        return parent::afficherErreur($messageErreur, "carte");
    }

    #[Route('/carte/suprression', name: 'supprimerCarte', methods: "GET")]
    public function supprimerCarte(): Response
    {
        $idCarte = $_REQUEST["idCarte"] ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $carte = $this->serviceCarte->recupererCarte($idCarte);
            $tableau = $carte->getColonne()->getTableau();
            $this->serviceUtilisateur->estParticipant($tableau);
            $cartes = $this->serviceCarte->supprimerCarte($tableau, $idCarte);
            if (count($cartes) > 0) {
                return ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
            } else {
                return ControleurCarte::redirection("tableau", "afficherListeMesTableaux");
            }
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection("base", "accueil");
        }
    }

    #[Route('/carte/nouveau', name: 'afficherFormulaireCreationCarte', methods: "GET")]
    public function afficherFormulaireCreationCarte(): Response
    {
        $idColonne = $_REQUEST['idColonne'] ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $colonne = $this->serviceColonne->recupererColonne($idColonne);
            $tableau = $colonne->getTableau();
            $this->serviceUtilisateur->estParticipant($tableau);
            $colonnes = $this->serviceColonne->recupererColonnesTableau($tableau->getIdTableau());
            return ControleurTableau::afficherVue('vueGenerale.php', [
                "pagetitle" => "Création d'une carte",
                "cheminVueBody" => "carte/formulaireCreationCarte.php",
                "colonne" => $colonne,
                "colonnes" => $colonnes
            ]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection("base", "accueil");
        }
    }

    #[Route('/carte/nouveau', name: 'creerCarte', methods: "POST")]
    public function creerCarte(): Response
    {
        $idColonne = $_REQUEST["idColonne"] ?? null;
        $attributs = [
            "titreCarte" => $_REQUEST["titreCarte"] ?? null,
            "descriptifCarte" => $_REQUEST["descriptifCarte"] ?? null,
            "couleurCarte" => $_REQUEST["couleurCarte"] ?? null,
            "affectationsCarte" => $_REQUEST["affectationsCarte"] ?? null,
        ];
        try {
            $this->serviceConnexion->pasConnecter();
            $colonne = $this->serviceColonne->recupererColonne($idColonne);
            $this->serviceCarte->recupererAttributs($attributs);
            $tableau = $colonne->getTableau();
            $this->serviceUtilisateur->estParticipant($tableau);
            $this->serviceCarte->creerCarte($tableau, $attributs, $colonne);
            return ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (CreationCarteException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurCarte::redirection("carte", "afficherFormulaireCreationCarte", ["idColonne" => $_REQUEST["idColonne"]]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection("base", 'accueil');
        }
    }

    #[Route('/carte/mettreAJour', name: 'afficherFormulaireMiseAJourCarte', methods: "GET")]
    public function afficherFormulaireMiseAJourCarte(): Response
    {
        $idCarte = $_REQUEST['idCarte'] ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $carte = $this->serviceCarte->recupererCarte($idCarte);
            $tableau = $carte->getColonne()->getTableau();
            $this->serviceUtilisateur->estParticipant($tableau);
            $colonnes = $this->serviceColonne->recupererColonnesTableau($tableau->getIdTableau());
            return ControleurTableau::afficherVue('vueGenerale.php', [
                "pagetitle" => "Modification d'une carte",
                "cheminVueBody" => "carte/formulaireMiseAJourCarte.php",
                "carte" => $carte,
                "colonnes" => $colonnes
            ]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection("base", "accueil");
        }
    }

    #[Route('/carte/mettreAJour', name: 'mettreAJourCarte', methods: "POST")]
    public function mettreAJourCarte(): Response
    {
        $idColonne = $_REQUEST["idColonne"] ?? null;
        $idCarte = $_REQUEST["idCarte"] ?? null;
        $attributs = [
            "titreCarte" => $_REQUEST["titreCarte"] ?? null,
            "descriptifCarte" => $_REQUEST["descriptifCarte"] ?? null,
            "couleurCarte" => $_REQUEST["couleurCarte"] ?? null,
            "affectationsCarte" => $_REQUEST["affectationsCarte"] ?? null,
        ];
        try {
            $this->serviceConnexion->pasConnecter();
            $colonne = $this->serviceColonne->recupererColonne($idColonne);
            $carte = $this->serviceCarte->verificationsMiseAJourCarte($idCarte, $colonne, $attributs);
            $tableau = $colonne->getTableau();
            $this->serviceUtilisateur->estParticipant($tableau);
            $this->serviceCarte->miseAJourCarte($tableau, $attributs, $carte, $colonne);
            return ControleurCarte::redirection("tableau", "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (CreationCarteException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("carte", "afficherFormulaireMiseAJourCarte", ['idCarte' => $idCarte]);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("tableau", "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (MiseAJourException $e) {
            MessageFlash::ajouter($e->getTypeMessageFlash(), $e->getMessage());
            return self::redirection("carte", 'afficherFormulaireCreationCarte', ["idColonne" => $colonne->getIdColonne()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection("base", "accueil");
        }
    }

}