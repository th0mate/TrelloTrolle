<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateurInterface;
use App\Trellotrolle\Lib\ConnexionUtilisateurSession;
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
use App\Trellotrolle\Service\Exception\TableauEception;
use App\Trellotrolle\Service\Exception\TableauException;
use App\Trellotrolle\Service\ServiceCarte;
use App\Trellotrolle\Service\ServiceCarteInterface;
use App\Trellotrolle\Service\ServiceConnexion;
use App\Trellotrolle\Service\ServiceConnexionInterface;
use App\Trellotrolle\Service\ServiceTableau;
use App\Trellotrolle\Service\ServiceTableauInterface;
use App\Trellotrolle\Service\ServiceUtilisateur;
use App\Trellotrolle\Service\ServiceUtilisateurInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ControleurTableau extends ControleurGenerique
{


    /**
     * ControleurTableau constructor.
     * @param ContainerInterface $container
     * @param ServiceTableauInterface $serviceTableau
     * @param ServiceConnexionInterface $serviceConnexion
     * @param ServiceUtilisateurInterface $serviceUtilisateur
     * @param ServiceCarteInterface $serviceCarte
     *
     * fonction qui permet de construire le controleur de tableau
     */

    public function __construct(ContainerInterface                    $container,
                                private ServiceTableauInterface       $serviceTableau,
                                private ServiceConnexionInterface     $serviceConnexion,
                                private ServiceUtilisateurInterface   $serviceUtilisateur,
                                private ServiceCarteInterface         $serviceCarte,
                                private ConnexionUtilisateurInterface $connexionUtilisateur
    )
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

    public function afficherErreur($messageErreur = "", $controleur = ""): Response
    {
        return parent::afficherErreur($messageErreur, "tableau");
    }

    /**
     * @param $codeTableau
     * @return Response
     *
     * fonction qui permet d'afficher un tableau avec son code
     */

    #[Route('/tableau/monTableau/{codeTableau}', name: 'afficherTableau', methods: "GET")]
    public function afficherTableau($codeTableau): Response
    {

        //$codeTableau = $_REQUEST["codeTableau"] ?? null;
        try {
            $tableau = $this->serviceTableau->recupererTableauParCode($codeTableau);
            $donnes = $this->serviceTableau->recupererCartesColonnes($tableau);
            $colaborateurs = $this->serviceUtilisateur->getParticipants($tableau);

            /*return ControleurTableau::afficherVue('vueGenerale.php', [
                "pagetitle" => "{$tableau->getTitreTableau()}",
                "cheminVueBody" => "tableau/tableau.php",
                "tableau" => $tableau,
                "colonnes" => $donnes["colonnes"],
                "participants" => $donnes["participants"],
                "data" => $donnes["data"],
            ]);*/
            return $this->afficherTwig('tableau/tableau.html.twig', ["tableau" => $tableau,
                "colonnes" => $donnes["colonnes"],
                "participants" => $donnes["participants"],
                "data" => $donnes["data"],
                "collaborateurs" => $colaborateurs]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection('accueil');
        }


    }

    /**
     * @return Response
     *
     * fonction qui permet d'afficher un formulaire de mise à jour d'un tableau
     */

    #[Route('/tableau/mettreAJour', name: 'afficherFormulaireMiseAJourTableau', methods: "GET")]
    public function afficherFormulaireMiseAJourTableau(): Response
    {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $tableau = $this->serviceTableau->recupererTableauParId($idTableau);
            $this->serviceUtilisateur->estParticipant($tableau,$this->connexionUtilisateur->getLoginUtilisateurConnecte());
            /*return ControleurTableau::afficherVue('vueGenerale.php', [
                "pagetitle" => "Modification d'un tableau",
                "cheminVueBody" => "tableau/formulaireMiseAJourTableau.php",
                "idTableau" => $_REQUEST["idTableau"],
                "nomTableau" => $tableau->getTitreTableau()
            ]);*/
            return $this->afficherTwig('tableau/formulaireMiseAJourTableau.html.twig', [
                "nomTableau" => $tableau->getTitreTableau()
            ]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurTableau::redirection("afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("accueil");
        }
    }

    /**
     * @return Response
     *
     * fonction qui permet d'afficher un formulaire de création d'un tableau
     */
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

    /**
     * @return Response
     *
     * fonction qui permet de creer un tableau
     */
    #[Route('/tableau/nouveau', name: 'creerTableau', methods: "POST")]
    public function creerTableau(): Response
    {
        $nomTableau = $_REQUEST["nomTableau"] ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $tableau = $this->serviceTableau->creerTableau($nomTableau, $this->connexionUtilisateur->getLoginUtilisateurConnecte());
            return ControleurTableau::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);

        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("afficherFormulaireCreationTableau");
        }
    }

    /**
     * @return Response
     *
     * fonction qui permet de mettre à jour un tableau
     */
    #[Route('/tableau/mettreAJour', name: 'mettreAJourTableau', methods: "POST")]
    public function mettreAJourTableau(): Response
    {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        $nomTableau = $_REQUEST["nomTableau"] ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $tableau = $this->serviceTableau->recupererTableauParId($idTableau);
            $this->serviceTableau->isNotNullNomTableau($nomTableau, $tableau);
            $estProprio = $this->serviceTableau->estParticipant($this->connexionUtilisateur->getLoginUtilisateurConnecte());
            if (!$estProprio) {
                MessageFlash::ajouter("danger", "Vous n'avez pas de droits d'éditions sur ce tableau");
            } else {
                $tableau->setTitreTableau($_REQUEST["nomTableau"]);
                $this->serviceTableau->mettreAJourTableau($tableau);
            }
            return ControleurTableau::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurTableau::redirection("afficherFormulaireMiseAJourTableau", ["idTableau" => $_REQUEST["idTableau"]]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("accueil");
        }
    }

    /**
     * @return Response
     *
     * fonction qui permet d'afficher un formulaire d'ajout d'un membre
     */
    #[Route('/tableau/inviter', name: 'afficherFormulaireAjoutMembre', methods: "GET")]
    public function afficherFormulaireAjoutMembre(): Response
    {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $tableau = $this->serviceTableau->recupererTableauParId($idTableau);
            $filtredUtilisateurs = $this->serviceUtilisateur->verificationsMembre($tableau, $this->connexionUtilisateur->getLoginUtilisateurConnecte());
            /*return ControleurTableau::afficherVue('vueGenerale.php', [
                "pagetitle" => "Ajout d'un membre",
                "cheminVueBody" => "tableau/formulaireAjoutMembreTableau.php",
                "tableau" => $tableau,
                "utilisateurs" => $filtredUtilisateurs
            ]);*/
            return $this->afficherTwig('tableau/formulaireAjoutMembreTableau.html.twig', [
                "utilisateurs" => $filtredUtilisateurs
            ]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurTableau::redirection("afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("accueil");
        }
    }

    /**
     * @return Response
     *
     * fonction qui permet d'ajouter un membre
     */
    #[Route('/tableau/inviter', name: 'ajouterMembreTableau', methods: "POST")]
    public function ajouterMembre(): Response
    {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        $login = $_REQUEST["login"] ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $tableau = $this->serviceTableau->recupererTableauParId($idTableau);
            $this->serviceUtilisateur->ajouterMembre($tableau, $login,$this->connexionUtilisateur->getLoginUtilisateurConnecte());
            return ControleurTableau::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("afficherTableau", ["codeTableau" => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("accueil");
        }
    }

    /**
     * @return Response
     *
     * fonction qui permet de supprimer un membre
     */

    #[Route('/tableau/supprimerMembre', name: 'supprimerMembre', methods: "GET")]
    public function supprimerMembre(): Response
    {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        $login = $_REQUEST["login"] ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $tableau = $this->serviceTableau->recupererTableauParId($idTableau);
            $utilisateur = $this->serviceUtilisateur->supprimerMembre($tableau, $login, $this->connexionUtilisateur->getLoginUtilisateurConnecte());
            $this->serviceCarte->miseAJourCarteMembre($tableau, $utilisateur);
            return ControleurTableau::redirection("afficherTableau", ["codeTableau" => $tableau->getCodeTableau()]);

        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (TableauException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("afficherTableau", ['codeTableau' => $e->getTableau()->getCodeTableau()]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("accueil");
        }
    }

    /**
     * @return Response
     *
     * fonction qui permet d'afficher la liste des tableaux
     */

    #[Route('/tableau', name: 'afficherListeMesTableaux', methods: "GET")]
    public function afficherListeMesTableaux(): Response
    {
        try {
            $this->serviceConnexion->pasConnecter();

            $tableaux = $this->serviceTableau->recupererTableauEstMembre($this->connexionUtilisateur->getLoginUtilisateurConnecte());
            /*return ControleurTableau::afficherVue('vueGenerale.php', [
                "pagetitle" => "Liste des tableaux de $login",
                "cheminVueBody" => "tableau/listeTableauxUtilisateur.php",
                "tableaux" => $tableaux
            ]);*/
            return $this->afficherTwig('tableau/listeTableauxUtilisateur.html.twig', ["tableaux" => $tableaux]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        }
    }

    /**
     * @param $idTableau
     * @return Response
     *
     * fonction qui permet de quitter un tableau via son id
     */

    #[Route('/{idTableau}/quitter', name: 'quitterTableau', methods: "GET")]
    public function quitterTableau($idTableau): Response
    {
        //$idTableau = $_REQUEST["idTableau"] ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $tableau = $this->serviceTableau->recupererTableauParId($idTableau);
            $utilisateur = $this->serviceUtilisateur->recupererUtilisateurParCle($this->connexionUtilisateur->getLoginUtilisateurConnecte());
            $this->serviceTableau->quitterTableau($tableau, $utilisateur);
            return ControleurTableau::redirection("afficherListeMesTableaux");

        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("afficherListeMesTableaux");
        }
    }

    /**
     * @param $idTableau
     * @return Response
     *
     * fonction qui permet de supprimer un tableau via son id
     */
    #[Route('/{idTableau}/suppression', name: 'supprimerTableau', methods: "GET")]
    public function supprimerTableau($idTableau): Response
    {
        try {
            $this->serviceConnexion->pasConnecter();
            $tableau = $this->serviceTableau->recupererTableauParId($idTableau);
            $this->serviceUtilisateur->estProprietaire($tableau, $this->connexionUtilisateur->getLoginUtilisateurConnecte());
            $this->serviceTableau->supprimerTableau($idTableau);
            return ControleurTableau::redirection("afficherListeMesTableaux");
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("afficherListeMesTableaux");
        }
    }
}