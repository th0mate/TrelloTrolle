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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ControleurCarteAPI
{

    public function __construct(
        private ServiceUtilisateurInterface $serviceUtilisateur,
        private ServiceConnexionInterface   $serviceConnexion,
        private ServiceCarteInterface       $serviceCarte,
        private ServiceColonneInterface     $serviceColonne,
        private ConnexionUtilisateurInterface $connexionUtilisateur
    )
    {
    }


    #[Route("/api/carte/supprimer", name: "supprimerCarteAPI", methods: "DELETE")]
    public function supprimerCarte(Request $request): Response
    {
        $jsondecode = json_decode($request->getContent());
        $idCarte = $jsondecode->idCarte;
        try {
            $this->serviceConnexion->pasConnecter();
            $carte = $this->serviceCarte->recupererCarte($idCarte);
            $tableau = $carte->getColonne()->getTableau();
            $this->serviceUtilisateur->estParticipant($tableau,$this->connexionUtilisateur->getLoginUtilisateurConnecte());
            $this->serviceCarte->supprimerCarte($tableau, $idCarte);
            return new JsonResponse('', 200);
        } catch (ServiceException $e) {
            return new JsonResponse(["error" => $e->getMessage()], $e->getCode());
        }
    }

    #[Route("/api/carte/modifier", name: "modifierCarteAPI", methods: "PATCH")]
    public function modifierCarte(Request $request): Response
    {

        $jsondecode = json_decode($request->getContent());
        $idCarte = $jsondecode->idCarte ?? null;
        $idColonne = $jsondecode->idColonne ?? null;
        $attributs = [
            "titreCarte" => $jsondecode->titreCarte ?? null,
            "descriptifCarte" => $jsondecode->descriptifCarte ?? null,
            "couleurCarte" => $jsondecode->couleurCarte ?? null,
            "affectationsCarte" => $jsondecode->affectationsCarte ?? null,
        ];
        try {
            $this->serviceConnexion->pasConnecter();
            $colonne = $this->serviceColonne->recupererColonne($idColonne);
            $carte = $this->serviceCarte->verificationsMiseAJourCarte($idCarte, $colonne, $attributs);
            $tableau = $colonne->getTableau();
            $this->serviceUtilisateur->estParticipant($tableau,$this->connexionUtilisateur->getLoginUtilisateurConnecte());
            $carte = $this->serviceCarte->miseAJourCarte($tableau, $attributs, $carte, $colonne);
            return new JsonResponse($carte, 200);
        } catch (ServiceException $e) {
            return new JsonResponse(["error" => $e->getMessage()], $e->getCode());
        }
    }

    #[Route("/api/carte/creer", name: "creerCarteAPI", methods: "POST")]
    public function creerCarte(Request $request): Response
    {

        $corps = $request->getContent();
        try {
            $jsondecode = json_decode($corps);
            $idColonne = $jsondecode->idColonne ?? null;
            $attributs = [
                "titreCarte" => $jsondecode->titreCarte ?? null,
                "descriptifCarte" => $jsondecode->descriptifCarte ?? null,
                "couleurCarte" => $jsondecode->couleurCarte ?? null,
                "affectationsCarte" => $jsondecode->affectationsCarte ?? null,
            ];
            $this->serviceConnexion->pasConnecter();
            $colonne = $this->serviceColonne->recupererColonne($idColonne);
            $this->serviceCarte->recupererAttributs($attributs);
            $tableau = $colonne->getTableau();
            $this->serviceUtilisateur->estParticipant($tableau,$this->connexionUtilisateur->getLoginUtilisateurConnecte());
            $carte = $this->serviceCarte->creerCarte($tableau, $attributs, $colonne);
            return new JsonResponse($carte, 200);
        } catch (ServiceException $e) {
            return new JsonResponse(["error" => $e->getMessage()], $e->getCode());
        }
    }

    #[Route("/api/carte/nextid", name: "getNextIdCarteAPI", methods: "POST")]
    public function getNextIdCarte(): Response
    {
        $idCarte = $this->serviceCarte->getNextIdCarte();
        return new JsonResponse(["idCarte" => $idCarte], 200);
    }

    #[Route("/api/carte/deplacer",name: "deplacerCarteAPI",methods: "PATCH")]
    public function deplacerCarte(Request $request): Response
    {
        $jsondecode=json_decode($request->getContent());
        $idCarte=$jsondecode->idCarte ??null;
        $idColonne=$jsondecode->idColonne ??null;
        try{
            $this->serviceConnexion->pasConnecter();
            $colonne=$this->serviceColonne->recupererColonne($idColonne);
            $carte=$this->serviceCarte->recupererCarte($idCarte);
            $tableau=$colonne->getTableau();
            $this->serviceUtilisateur->estParticipant($tableau, $this->connexionUtilisateur->getLoginUtilisateurConnecte());
            $carte->setColonne($colonne);
            $this->serviceCarte->deplacerCarte($carte,$colonne);
            return new JsonResponse('',200);
        } catch (ServiceException $e) {
            return new JsonResponse(["error" => $e->getMessage()], $e->getCode());
        }
    }

    #[Route("/api/carte/affectations",name: "getAffectationsCarteAPI",methods:"POST")]
    public function getAffectations(Request $request):Response
    {
        $josndecode=json_decode($request->getContent());
        $idCarte=$josndecode->idCarte ??null;
        try {
            $this->serviceConnexion->pasConnecter();
            $carte=$this->serviceCarte->recupererCarte($idCarte);
            $affectations=$this->serviceCarte->getAffectations($carte);
            return new JsonResponse($affectations,200);
        }catch (ServiceException $e){
            return new JsonResponse(["error"=>$e->getMessage()],$e->getCode());
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