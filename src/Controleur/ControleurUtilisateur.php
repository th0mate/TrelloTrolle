<?php

namespace App\Trellotrolle\Controleur;

use App\SAE\Lib\ConnexionUtilisateur;
use App\SAE\Model\Repository\EntrepriseRepository;
use App\Trellotrolle\Lib\ConnexionUtilisateurInterface;
use App\Trellotrolle\Lib\ConnexionUtilisateurSession;
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
use App\Trellotrolle\Service\Exception\ConnexionException;
use App\Trellotrolle\Service\Exception\CreationException;
use App\Trellotrolle\Service\Exception\MiseAJourException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\ServiceConnexion;
use App\Trellotrolle\Service\ServiceConnexionInterface;
use App\Trellotrolle\Service\ServiceUtilisateur;
use App\Trellotrolle\Service\ServiceUtilisateurInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ControleurUtilisateur extends ControleurGenerique
{

    /**
     * ControleurUtilisateur constructor.
     * @param ContainerInterface $container le conteneur de dépendances
     * @param ServiceConnexionInterface $serviceConnexion le service de connexion
     * @param ServiceUtilisateurInterface $serviceUtilisateur le service utilisateur
     *
     * fonction qui permet de construire le controleur de l'utilisateur
     */


    public function __construct(ContainerInterface                    $container,
                                private ServiceConnexionInterface     $serviceConnexion,
                                private ServiceUtilisateurInterface   $serviceUtilisateur,
                                private ConnexionUtilisateurInterface $connexionUtilisateur
    )
    {
        parent::__construct($container);

    }

    /**
     * @return Response l'affichage de l'erreur
     *
     * fonction qui permet d'afficher la page d'erreur
     */
    public function afficherErreur($messageErreur = "", $controleur = ""): Response
    {
        return parent::afficherErreur($messageErreur, "utilisateur");
    }


    /**
     * @return Response La redirection
     *
     * fonction qui permet d'afficher les détails de l'utilisateur
     */

    #[Route('/profile', name: 'afficherDetail', methods: "GET")]
    public function afficherDetail(): Response
    {
        try {
            $this->serviceConnexion->pasConnecter();
            $login = $this->connexionUtilisateur->getLoginUtilisateurConnecte();
            $utilisateur = $this->serviceUtilisateur->recupererUtilisateurParCle($login);

            return $this->afficherTwig('utilisateur/detail.html.twig', ["utilisateur" => $utilisateur]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        }
    }

    /**
     * @return Response La redirection
     *
     * fonction qui permet d'afficher le formulaire d'incription
     */

    #[Route('/inscription', name: 'afficherFormulaireCreation', methods: "GET")]
    public function afficherFormulaireCreation(): Response
    {
        try {
            $this->serviceConnexion->dejaConnecte();

            return $this->afficherTwig('utilisateur/formulaireCreation.html.twig');
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        }
    }

    /**
     * @return Response La redirection
     *
     * fonction qui permet de creer un utilisateur grâce au formulaire
     */

    #[Route('/inscription', name: 'creerDepuisFormulaire', methods: "POST")]
    public function creerDepuisFormulaire(): Response
    {
        $attributs = [
            "login" => $_REQUEST["login"] ?? null,
            "nom" => $_REQUEST["nom"] ?? null,
            "prenom" => $_REQUEST["prenom"] ?? null,
            "email" => $_REQUEST["email"] ?? null,
            "mdp" => $_REQUEST["mdp"] ?? null,
            "mdp2" => $_REQUEST["mdp2"] ?? null,
        ];
        try {
            $this->serviceConnexion->dejaConnecte();
            $this->serviceUtilisateur->creerUtilisateur($attributs);
            MessageFlash::ajouter("success", "L'utilisateur a bien été créé !");
            return ControleurUtilisateur::redirection("afficherFormulaireConnexion");
        } catch (ConnexionException $e) {
            MessageFlash::ajouter("info", $e->getMessage());
            return self::redirection("afficherListeMesTableaux");
        } catch (CreationException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("afficherFormulaireCreation");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection("afficherFormulaireCreation");
        }
    }

    /**
     * @return Response La redirection
     *
     * fonction qui permet d'afficher le formulaire de mise à jour de l'utilisateur
     */
    #[Route('/profile/miseAJour', name: 'afficherFormulaireMiseAJourUtilisateur', methods: "GET")]
    public function afficherFormulaireMiseAJour(): Response
    {
        try {
            $this->serviceConnexion->pasConnecter();
            $utilisateur = $this->serviceUtilisateur->recupererUtilisateurParCle($this->connexionUtilisateur->getLoginUtilisateurConnecte());

            return $this->afficherTwig('utilisateur/formulaireMiseAjour.html.twig', ["utilisateur" => $utilisateur]);
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        }
    }

    /**
     * @return Response La redirection
     *
     * fonction qui permet de mettre à jour l'utilisateur
     */
    #[Route('/profile/miseAJour', name: 'mettreAJour', methods: "POST")]
    public function mettreAJour(): Response
    {
        $attributs = [
            "login" => $_REQUEST["login"] ?? null,
            "nom" => $_REQUEST["nom"] ?? null,
            "prenom" => $_REQUEST["prenom"] ?? null,
            "email" => $_REQUEST["email"] ?? null,
        ];
        try {
            $this->serviceConnexion->pasConnecter();
            $this->serviceUtilisateur->mettreAJourUtilisateur($attributs);
            MessageFlash::ajouter("success", "L'utilisateur a bien été modifié !");
            return self::redirection("afficherListeMesTableaux");
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (MiseAJourException $e) {
            MessageFlash::ajouter($e->getTypeMessageFlash(), $e->getMessage());
            return self::redirection("afficherFormulaireMiseAJourUtilisateur");
        }
    }

    /**
     * @return Response La redirection
     *
     * fonction qui permet de supprimer l'utilisateur
     */

    #[Route('/profile/supprimer/{login}', name: 'supprimer', methods: "GET")]
    public function supprimer($login): Response
    {
        try {
            $this->serviceConnexion->pasConnecter();
            $this->serviceUtilisateur->supprimerUtilisateur($login);
            $this->serviceConnexion->deconnecter();
            MessageFlash::ajouter("success", "Votre compte a bien été supprimé !");
            return self::redirection("afficherFormulaireConnexion");
        } catch (ConnexionException $e) {
            return self::redirectionConnectionFlash($e);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection("afficherDetail");
        }
    }

    /**
     * @return Response La redirection
     *
     * fonction qui permet d'afficher le formulaire de connexion
     */

    #[Route('/connexion', name: 'afficherFormulaireConnexion', methods: "GET")]
    public function afficherFormulaireConnexion(): Response
    {
        try {
            $this->serviceConnexion->dejaConnecte();
            return $this->afficherTwig("utilisateur/formulaireConnexion.html.twig");
        } catch (ConnexionException $e) {
            MessageFlash::ajouter("info", $e->getMessage());
            return self::redirection("afficherListeMesTableaux");
        }
    }

    /**
     * @return Response La redirection
     *
     * fonction qui permet de se connecter
     */

    #[Route('/connexion', name: 'connecter', methods: "POST")]
    public function connecter(): Response
    {
        $login = $_REQUEST["login"] ?? null;
        $mdp = $_REQUEST["mdp"] ?? null;
        try {
            $this->serviceConnexion->dejaConnecte();
            $this->serviceConnexion->connecter($login, $mdp);
            MessageFlash::ajouter("success", "Connexion effectuée.");
            return self::redirection("afficherListeMesTableaux");
        } catch (ConnexionException $e) {
            return self::redirection("afficherListeMesTableaux");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection("afficherFormulaireConnexion");
        }
    }


    /**
     * @return Response La redirection
     *
     * fonction qui permet de se deconnecter
     */
    #[Route('/deconnexion', name: 'deconnexion', methods: "GET")]
    public function deconnecter(): Response
    {
        try {
            $this->serviceConnexion->deconnecter();
            MessageFlash::ajouter("success", "L'utilisateur a bien été déconnecté.");
            return self::redirection("accueil");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return self::redirection("accueil");
        }
    }


    /**
     * @return Response La redirection
     *
     * fonction qui permet d'afficher le formulaire de recuperation de compte
     */
    #[Route('/recuperation', name: 'utilisateurResetCompte', methods: "GET")]
    public function afficherFormulaireRecuperationCompte(): Response
    {
        try {
            $this->serviceConnexion->dejaConnecte();
            return $this->afficherTwig('utilisateur/resetCompte.html.twig');
        } catch (ConnexionException $e) {
            MessageFlash::ajouter("info", $e->getMessage());
            return self::redirection("afficherListeMesTableaux");
        }
    }


    /**
     * @return Response La redirection
     *
     * fonction qui permet de recuperer le compte
     */
    #[Route('/recuperation', name: 'recupererCompte', methods: "POST")]
    public function recupererCompte(): Response
    {
        $mail = $_REQUEST["email"] ?? null;
        try {
            $this->serviceConnexion->dejaConnecte();
            $this->serviceUtilisateur->recupererCompte($mail);

            MessageFlash::ajouter("success", "Un e-mail a été envoyé à l'adresse indiquée.");
            return self::redirection();
        } catch (ConnexionException $e) {
            MessageFlash::ajouter("info", $e->getMessage());
            return self::redirection("afficherListeMesTableaux");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection("afficherFormulaireConnexion");
        }
    }

    #[Route('/recuperationMdp', name: 'changerMotDePasse', methods: "GET")]
    public function verifNonce(): Response
    {
        $nonce = $_REQUEST["nonce"] ?? null;
        $login = $_REQUEST["login"] ?? null;
        try {
            if (!$this->connexionUtilisateur->estConnecte()) {
                $this->serviceUtilisateur->verifNonce($login, $nonce);
            }
            if ($this->connexionUtilisateur->getLoginUtilisateurConnecte()!=$login){
                MessageFlash::ajouter("warning","Ce login n'est pas le votre" );
                return self::redirection("afficherFormulaireConnexion");
            }
            return $this->afficherTwig('utilisateur/resultatResetCompte.html.twig', ["login" => $login]);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection("afficherFormulaireConnexion");
        }
    }

    /**
     * Réinitialise le mot de passe d'un utilisateur.
     *
     * @return Response
     */
    #[Route('/recuperationMdp', name: 'validerMDP', methods: "POST")]
    public function resetPassword(): Response
    {
        $login = $_REQUEST["login"] ?? null;
        $mdp = $_REQUEST["mdp"] ?? null;
        $mdp2 = $_REQUEST["mdp2"] ?? null;
        $oldmdp = $_REQUEST["mdpAncien"] ?? null;
        try {
            if ($this->connexionUtilisateur->getLoginUtilisateurConnecte()!=$login){
                MessageFlash::ajouter("warning","Ce login n'est pas le votre" );
                return self::redirection("afficherFormulaireConnexion");
            }
            $utilisateur = $this->serviceUtilisateur->recupererUtilisateurParCle($login);
            if (!(MotDePasse::verifier($oldmdp, $utilisateur->getMdpHache()))) {
                MessageFlash::ajouter("warning","l'ancien mot de passe est erroné" );
                return self::redirection("changerMotDePasse",["login"=>$login]);
            }
            $this->serviceUtilisateur->changerMotDePasse($login, $mdp, $mdp2);
            MessageFlash::ajouter("success", "Le mot de passe a bien été modifié !");
            if($this->connexionUtilisateur->estConnecte()) {
                return self::redirection("afficherListeMesTableaux");
            }
            return self::redirection("connecter");

        } catch (ConnexionException $e) {
            MessageFlash::ajouter("info", $e->getMessage());
            return self::redirection("accueil");
        } catch (ServiceException $e){
            MessageFlash::ajouter("warning", $e->getMessage());
            return self::redirection("changerMotDePasse",["login"=>$login]);
        }
    }


}