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
use App\Trellotrolle\Service\Exception\ConnexionException;
use App\Trellotrolle\Service\Exception\CreationException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;
use App\Trellotrolle\Service\ServiceCarte;
use App\Trellotrolle\Service\ServiceColonne;
use App\Trellotrolle\Service\ServiceColonneInterface;
use App\Trellotrolle\Service\ServiceConnexion;
use App\Trellotrolle\Service\ServiceConnexionInterface;
use App\Trellotrolle\Service\ServiceTableau;
use App\Trellotrolle\Service\ServiceTableauInterface;
use App\Trellotrolle\Service\ServiceUtilisateur;
use App\Trellotrolle\Service\ServiceUtilisateurInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ControleurColonne extends ControleurGenerique
{

    /**
     * ControleurColonne constructor.
     * @param ContainerInterface $container
     * @param ServiceConnexionInterface $serviceConnexion
     * @param ServiceColonneInterface $serviceColonne
     * @param ServiceUtilisateurInterface $serviceUtilisateur
     * @param ServiceTableauInterface $serviceTableau
     *
     * fonction qui permet de construire le controleur de colonne
     */

    public function __construct(ContainerInterface         $container,
                                private ServiceConnexionInterface   $serviceConnexion,
                                private ServiceColonneInterface     $serviceColonne,
                                private ServiceUtilisateurInterface $serviceUtilisateur,
                                private ServiceTableauInterface     $serviceTableau)
    {
        parent::__construct($container);

    }


    /**
     * @param string $messageErreur
     * @param string $controleur
     * @return Response
     *
     * fonction qui permet d'afficher une erreur
     */

    public function afficherErreur($messageErreur = "", $controleur = ""): \Symfony\Component\HttpFoundation\Response
    {
        return parent::afficherErreur($messageErreur, "colonne");
    }


    /**
     * @param Request $request
     * @return Response
     *
     * fonction qui permet de supprimer une colonne
     */

    #[Route('/colonne/suppression', name: 'supprimerColonne', methods: "GET")]
    public function supprimerColonne(): Response
    {
        $idColonne = $_REQUEST["idColonne"] ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $colonne = $this->serviceColonne->recupererColonne($idColonne);
            $tableau = $colonne->getTableau();
            $this->serviceUtilisateur->estParticipant($tableau);
            $nbColonnes = $this->serviceColonne->supprimerColonne($tableau, $idColonne);
            if ($nbColonnes > 0) {
                return ControleurColonne::redirection( "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
            } else {
                return ControleurCarte::redirection(  "afficherListeMesTableaux");
            }
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection(  "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection( "accueil");
        }
    }

    /**
     * @return Response
     *
     * fonction qui permet d'afficher le formulaire de création d'une colonne
     */

    #[Route('/colonne/nouveau', name: 'afficherFormulaireCreationColonne', methods: "GET")]
    public function afficherFormulaireCreationColonne(): Response
    {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $tableau = $this->serviceTableau->recupererTableauParId($idTableau);
            $this->serviceUtilisateur->estParticipant($tableau);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection(  "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection( "accueil");
        }
        /*return ControleurTableau::afficherVue('vueGenerale.php', [
            "pagetitle" => "Création d'une colonne",
            "cheminVueBody" => "colonne/formulaireCreationColonne.php",
            "idTableau" => $_REQUEST["idTableau"],
        ]);*/
        return $this->afficherTwig('colonne/formulaireCreationColonne.html.twig',["idTableau" => $_REQUEST["idTableau"]]);
    }


    /**
     * @return Response
     *
     * fonction qui permet de créer une colonne
     */

    #[Route('/colonne/nouveau', name: 'creerColonne', methods: "POST")]
    public function creerColonne(): Response
    {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        $nomColonne = $_REQUEST["nomColonne"] ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $tableau = $this->serviceTableau->recupererTableauParId($idTableau);
            $this->serviceColonne->isSetNomColonne($nomColonne);
            $this->serviceUtilisateur->estParticipant($tableau);
            $colonne = $this->serviceColonne->creerColonne($tableau, $nomColonne);
            //(new ServiceCarte())->newCarte($colonne,["Exemple","Exemple de carte","#FFFFFF",[]]);
            return ControleurColonne::redirection(  "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (CreationException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurColonne::redirection( "afficherFormulaireCreationColonne", ["idTableau" => $_REQUEST["idTableau"]]);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection(  "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("accueil");
        }
    }


    /**
     * @return Response
     *
     * fonction qui permet d'afficher le formulaire de mise à jour d'une colonne
     */

    #[Route('/colonne/mettreAJour', name: 'afficherFormulaireMiseAJourColonne', methods: "GET")]
    public function afficherFormulaireMiseAJourColonne(): Response
    {
        $idColonne = $_REQUEST["idColonne"] ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $colonne = $this->serviceColonne->recupererColonne($idColonne);
            $tableau = $colonne->getTableau();
            $this->serviceUtilisateur->estParticipant($tableau);
            /*return ControleurTableau::afficherVue('vueGenerale.php', [
                "pagetitle" => "Modification d'une colonne",
                "cheminVueBody" => "colonne/formulaireMiseAJourColonne.php",
                "idColonne" => $idColonne,
                "nomColonne" => $colonne->getTitreColonne()
            ]);*/
            return $this->afficherTwig('colonne/formulaireMiseAJourColonne.html.twig',[
                "idColonne" => $idColonne,
                "nomColonne" => $colonne->getTitreColonne()
            ]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurColonne::redirection(  "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection( "accueil");
        }

    }

    /**
     * @return Response
     *
     * fonction qui permet de mettre à jour une colonne
     */

    #[Route('/colonne/mettreAJour', name: 'mettreAJourColonne', methods: "POST")]
    public function mettreAJourColonne(): Response
    {
        $idColonne = $_REQUEST["idColonne"] ?? null;
        $nomColonne = $_REQUEST["nomColonne"] ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $colonne = $this->serviceColonne->recupererColonneAndNomColonne($idColonne, $nomColonne);
            $tableau = $colonne->getTableau();
            $this->serviceUtilisateur->estParticipant($tableau);
            $colonne->setTitreColonne($nomColonne);
            $this->serviceColonne->miseAJourColonne($colonne);
            return ControleurColonne::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (CreationException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurColonne::redirection( "afficherFormulaireMiseAJourColonne", ["idColonne" => $idColonne]);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurColonne::redirection(  "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("accueil");
        }
    }
}