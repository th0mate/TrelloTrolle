<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateurInterface;
use App\Trellotrolle\Lib\ConnexionUtilisateurSession;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Service\Exception\ConnexionException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;
use App\Trellotrolle\Service\ServiceCarte;
use App\Trellotrolle\Service\ServiceCarteInterface;
use App\Trellotrolle\Service\ServiceConnexion;
use App\Trellotrolle\Service\ServiceConnexionInterface;
use App\Trellotrolle\Service\ServiceTableau;
use App\Trellotrolle\Service\ServiceTableauInterface;
use App\Trellotrolle\Service\ServiceUtilisateur;
use App\Trellotrolle\Service\ServiceUtilisateurInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ControleurTableauAPI
{

    /**
     * ControleurTableauAPI constructor.
     * @param ServiceConnexionInterface $serviceConnexion
     * @param ServiceTableauInterface $serviceTableau
     * @param ServiceUtilisateurInterface $serviceUtilisateur
     * @param ServiceCarteInterface $serviceCarte
     *
     * fonction qui permet de construire le controleur de tableau avec l'API
     */

    public function __construct(
        private ServiceConnexionInterface     $serviceConnexion,
        private ServiceTableauInterface       $serviceTableau,
        private ServiceUtilisateurInterface   $serviceUtilisateur,
        private ServiceCarteInterface         $serviceCarte,
        private ConnexionUtilisateurInterface $connexionUtilisateur

    )
    {
    }

    /**
     * @return JsonResponse
     *
     * fonction qui permet de creer un tableau avec l'API
     */
    #[Route("/api/tableau/membre/ajouter", name: "ajouterMembreAPI", methods: "PATCH")]
    public function ajouterMembre(Request $request)
    {
        {
            $jsondecode = json_decode($request->getContent());
            $idTableau = $jsondecode->idTableau ?? null;
            $login = $jsondecode->login ?? null;
            try {
                $this->serviceConnexion->pasConnecter();
                $tableau = $this->serviceTableau->recupererTableauParId($idTableau);
                $this->serviceUtilisateur->ajouterMembre($tableau, $login, $this->connexionUtilisateur->getLoginUtilisateurConnecte());
                return new JsonResponse('', 200);
            } catch (ServiceException $e) {
                return new JsonResponse(["error" => $e->getMessage()], $e->getCode());
            }
        }
    }

    /**
     * @return JsonResponse
     *
     * fonction qui permet de supprimer un membre avec l'API
     */

    #[Route('/api/tableau/membre/supprimer', name: 'supprimerMembreAPI', methods: "PATCH")]
    public function supprimerMembre(Request $request)
    {
        $jsondecode = json_decode($request->getContent());
        $idTableau = $jsondecode->idTableau ?? null;
        $login = $jsondecode->login ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $tableau = $this->serviceTableau->recupererTableauParId($idTableau);
            $utilisateur = $this->serviceUtilisateur->supprimerMembre($tableau, $login, $this->connexionUtilisateur->getLoginUtilisateurConnecte());
            $this->serviceCarte->miseAJourCarteMembre($tableau, $utilisateur);
            return new JsonResponse("", 200);

        } catch (ServiceException $e) {
            return new JsonResponse(["error" => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     *  fonction qui permet de recuperer les membres d'un tableau avec l'API
     */
    #[Route('/api/tableau/membre/getPourTableau', name: 'getMembresTableau', methods: "POST")]
    public function getMembresTableau(Request $request)
    {
        $jsondecode = json_decode($request->getContent());
        $idTableau = $jsondecode->idTableau ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $tableau = $this->serviceTableau->recupererTableauParId($idTableau);
            $membres = $this->serviceUtilisateur->getParticipants($tableau);
            return new JsonResponse($membres, 200);
        } catch (ServiceException $e) {
            return new JsonResponse(["error" => $e->getMessage()], $e->getCode());
        }
    }

/**
     * @param Request $request
     * @return JsonResponse
     *
     *  fonction qui permet de recuperer le proprietaire d'un tableau avec l'API
     */


    #[Route('/api/tableau/membre/getProprio', name: 'getProprietaireTableau', methods: "POST")]
    public function getProprietaireTableau(Request $request)
    {
        $jsondecode = json_decode($request->getContent());
        $idTableau = $jsondecode->idTableau ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $tableau = $this->serviceTableau->recupererTableauParId($idTableau);
            $proprietaire = $this->serviceUtilisateur->getProprietaireTableau($tableau);
            return new JsonResponse($proprietaire, 200);
        } catch (ServiceException $e) {
            return new JsonResponse(["error" => $e->getMessage()], $e->getCode());
        }
    }



}

