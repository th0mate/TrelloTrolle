<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateurInterface;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Service\Exception\ConnexionException;
use App\Trellotrolle\Service\Exception\CreationException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;
use App\Trellotrolle\Service\ServiceCarteInterface;
use App\Trellotrolle\Service\ServiceColonne;
use App\Trellotrolle\Service\ServiceColonneInterface;
use App\Trellotrolle\Service\ServiceConnexion;
use App\Trellotrolle\Service\ServiceConnexionInterface;
use App\Trellotrolle\Service\ServiceTableau;
use App\Trellotrolle\Service\ServiceTableauInterface;
use App\Trellotrolle\Service\ServiceUtilisateur;
use App\Trellotrolle\Service\ServiceUtilisateurInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ControleurColonneAPI
{

    public function __construct(
        private ServiceConnexionInterface     $serviceConnexion,
        private ServiceColonneInterface       $serviceColonne,
        private ServiceUtilisateurInterface   $serviceUtilisateur,
        private ServiceTableauInterface       $serviceTableau,
        private ConnexionUtilisateurInterface $connexionUtilisateur

    )
    {
    }

    #[Route("/api/colonne/creer", name: "creerColonneAPI", methods: "PUT")]
    public function creerColonne(Request $request): Response
    {
        $jsondecode = json_decode($request->getContent());
        $idTableau = $jsondecode->idTableau ?? null;
        $nomColonne = $jsondecode->nomColonne ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $tableau = $this->serviceTableau->recupererTableauParId($idTableau);
            $this->serviceColonne->isSetNomColonne($nomColonne);
            $this->serviceUtilisateur->estParticipant($tableau, $this->connexionUtilisateur->getLoginUtilisateurConnecte());
            $colonne = $this->serviceColonne->creerColonne($tableau, $nomColonne);
            return new JsonResponse($colonne, 200);
        } catch (ServiceException $e) {
            return new JsonResponse(["error" => $e->getMessage()], $e->getCode());
        }
    }

    #[Route("/api/colonne/supprimer", name: "supprimerColonneAPI", methods: "DELETE")]
    public function supprimerColonne(Request $request): Response
    {
        $jsondecode = json_decode($request->getContent());
        $idColonne = $jsondecode->idColonne ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $colonne = $this->serviceColonne->recupererColonne($idColonne);
            $tableau = $colonne->getTableau();
            $this->serviceUtilisateur->estParticipant($tableau, $this->connexionUtilisateur->getLoginUtilisateurConnecte());
            $this->serviceColonne->supprimerColonne($tableau, $idColonne);
            return new JsonResponse('', 200);
        } catch (ServiceException $e) {
            return new JsonResponse(["error" => $e->getMessage()], $e->getCode());
        }
    }

    #[Route("/api/colonne/modifier", name: "modifierColonneAPI", methods: "PATCH")]
    public function modifierColonne(Request $request): Response
    {
        $jsondecode = json_decode($request->getContent());
        $nomColonne = $jsondecode->nomColonne ?? null;
        $idColonne = $jsondecode->idColonne ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $colonne = $this->serviceColonne->recupererColonneAndNomColonne($idColonne, $nomColonne);
            $tableau = $colonne->getTableau();
            $this->serviceUtilisateur->estParticipant($tableau, $this->connexionUtilisateur->getLoginUtilisateurConnecte());
            $colonne->setTitreColonne($nomColonne);
            $colonne = $this->serviceColonne->miseAJourColonne($colonne);
            return new JsonResponse($colonne, 200);
        } catch (ServiceException $e) {
            return new JsonResponse(["error" => $e->getMessage()], $e->getCode());
        }
    }

    #[Route("/api/colonne/nextid", name: "getNextIdColonneAPI", methods: "POST")]
    public function getNextIdColonne(): Response
    {
        $idColonne = $this->serviceColonne->getNextIdColonne();
        return new JsonResponse(["idColonne" => $idColonne], 200);
    }

    #[Route("/api/colonne/inverser", name: "inverserOrdreColonnesAPI", methods: "PATCH")]
    public function inverserOrdreColonnes(Request $request): Response
    {
        $jsondecode = json_decode($request->getContent());
        $idColonne1 = $jsondecode->idColonne1 ?? null;
        $idColonne2 = $jsondecode->idColonne2 ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $colonne1 = $this->serviceColonne->recupererColonne($idColonne1);
            $colonne2 = $this->serviceColonne->recupererColonne($idColonne2);
            $tableau = $colonne1->getTableau();
            $this->serviceUtilisateur->estParticipant($tableau, $this->connexionUtilisateur->getLoginUtilisateurConnecte());
            $this->serviceColonne->inverserOrdreColonnes($idColonne1, $idColonne2);
            return new JsonResponse('', 200);
        } catch (ServiceException $e) {
            return new JsonResponse(["error" => $e->getMessage()], $e->getCode());
        }
    }
}