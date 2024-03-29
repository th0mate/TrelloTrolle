<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateurInterface;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Service\Exception\ConnexionException;
use App\Trellotrolle\Service\Exception\CreationException;
use App\Trellotrolle\Service\Exception\MiseAJourException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;
use App\Trellotrolle\Service\ServiceCarte;
use App\Trellotrolle\Service\ServiceCarteInterface;
use App\Trellotrolle\Service\ServiceColonne;
use App\Trellotrolle\Service\ServiceColonneInterface;
use App\Trellotrolle\Service\ServiceConnexion;
use App\Trellotrolle\Service\ServiceConnexionInterface;
use App\Trellotrolle\Service\ServiceUtilisateur;
use App\Trellotrolle\Service\ServiceUtilisateurInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class ControleurCarte extends ControleurGenerique
{

    /**
     * ControleurCarte constructor.
     * @param ContainerInterface $container
     * @param ServiceConnexionInterface $serviceConnexion
     * @param ServiceCarteInterface $serviceCarte
     * @param ServiceUtilisateurInterface $serviceUtilisateur
     * @param ServiceColonneInterface $serviceColonne
     *
     * fonction qui permet de construire le controleur de carte
     */

    public function __construct(ContainerInterface         $container,
                                private ServiceConnexionInterface   $serviceConnexion,
                                private ServiceCarteInterface      $serviceCarte,
                                private ServiceUtilisateurInterface $serviceUtilisateur,
                                private ServiceColonneInterface     $serviceColonne,
                                private ConnexionUtilisateurInterface $connexionUtilisateur)
    {
        parent::__construct($container);
    }

    /**
     * @param string $messageErreur
     * @param string $controleur
     * @return Response
     *
     * fonction permettant d'afficher une erreur avec un message et un controleur donné
     */
    public function afficherErreur($messageErreur = "", $controleur = ""): Response
    {
        return parent::afficherErreur($messageErreur, "carte");
    }

    /**
     * @return Response
     *
     * fonction permettant de supprimer une carte
     */

    #[Route('/carte/suprression', name: 'supprimerCarte', methods: "GET")]
    //TODO supprimer cette fonction car elle est maintenant dans l'API
    public function supprimerCarte(): Response
    {
        $idCarte = $_REQUEST["idCarte"] ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $carte = $this->serviceCarte->recupererCarte($idCarte);
            $tableau = $carte->getColonne()->getTableau();
            $this->serviceUtilisateur->estParticipant($tableau,$this->connexionUtilisateur->getLoginUtilisateurConnecte());
            $cartes = $this->serviceCarte->supprimerCarte($tableau, $idCarte);
            if (count($cartes) > 0) {
                return ControleurCarte::redirection(  "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
            } else {
                return ControleurCarte::redirection(  "afficherListeMesTableaux");
            }
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurCarte::redirection(  "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection( "accueil");
        }
    }

    /**
     * @return Response
     *
     * fonction permettant d'afficher le formulaire de création d'une carte
     */

    #[Route('/carte/nouveau', name: 'afficherFormulaireCreationCarte', methods: "GET")]
    public function afficherFormulaireCreationCarte(): Response
    {
        $idColonne = $_REQUEST['idColonne'] ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $colonne = $this->serviceColonne->recupererColonne($idColonne);
            $tableau = $colonne->getTableau();
            $this->serviceUtilisateur->estParticipant($tableau,$this->connexionUtilisateur->getLoginUtilisateurConnecte());
            $colonnes = $this->serviceColonne->recupererColonnesTableau($tableau->getIdTableau());
            /*return ControleurTableau::afficherVue('vueGenerale.php', [
                "pagetitle" => "Création d'une carte",
                "cheminVueBody" => "carte/formulaireCreationCarte.php",
                "colonne" => $colonne,
                "colonnes" => $colonnes
            ]);*/
            return $this->afficherTwig('carte/formulaireCreationCarte.html.twig',["colonne" => $colonne,
                "colonnes" => $colonnes]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection(  "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection( "accueil");
        }
    }

    /**
     * @return Response
     *
     * fonction permettant de créer une carte
     */

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
            $this->serviceUtilisateur->estParticipant($tableau,$this->connexionUtilisateur->getLoginUtilisateurConnecte());
            $this->serviceCarte->creerCarte($tableau, $attributs, $colonne);
            return ControleurCarte::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection(  "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (CreationException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurCarte::redirection(   "afficherFormulaireCreationCarte", ["idColonne" => $_REQUEST["idColonne"]]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection( 'accueil');
        }
    }

    /**
     * @return Response
     *
     * fonction permettant d'afficher le formulaire de mise à jour d'une carte
     */

    #[Route('/carte/mettreAJour', name: 'afficherFormulaireMiseAJourCarte', methods: "GET")]
    public function afficherFormulaireMiseAJourCarte(): Response
    {
        $idCarte = $_REQUEST['idCarte'] ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $carte = $this->serviceCarte->recupererCarte($idCarte);
            $tableau = $carte->getColonne()->getTableau();
            $this->serviceUtilisateur->estParticipant($tableau,$this->connexionUtilisateur->getLoginUtilisateurConnecte());
            $colonnes = $this->serviceColonne->recupererColonnesTableau($tableau->getIdTableau());
            return $this->afficherTwig('carte/formulaireMiseAJourCarte.html.twig',[ "carte" => $carte,
                "colonnes" => $colonnes]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection(  "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection( "accueil");
        }
    }

    /**
     * @return Response
     *
     * fonction permettant de mettre à jour une carte
     */

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
            $this->serviceUtilisateur->estParticipant($tableau,$this->connexionUtilisateur->getLoginUtilisateurConnecte());
            $this->serviceCarte->miseAJourCarte($tableau, $attributs, $carte, $colonne);
            return ControleurCarte::redirection(  "afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (CreationException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection(   "afficherFormulaireMiseAJourCarte", ['idCarte' => $idCarte]);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection(  "afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (MiseAJourException $e) {
            MessageFlash::ajouter($e->getTypeMessageFlash(), $e->getMessage());
            return self::redirection(   'afficherFormulaireCreationCarte', ["idColonne" => $colonne->getIdColonne()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection( "accueil");
        }
    }

    #[Route("/api/carte/getCarte", name: "getCarteAPI", methods: "POST")]
    public function getCarte(Request $request): Response
    {
        $jsondecode = json_decode($request->getContent());
        $idCarte = $jsondecode->idCarte ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $carte = $this->serviceCarte->recupererCarte($idCarte);
            return new JsonResponse($carte, 200);
        } catch (ServiceException $e) {
            return new JsonResponse(["error" => $e->getMessage()], $e->getCode());
        }
    }

}