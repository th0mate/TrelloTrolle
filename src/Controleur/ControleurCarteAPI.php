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

    /**
     * ControleurCarteAPI constructor.
     * @param ServiceUtilisateurInterface $serviceUtilisateur
     * @param ServiceConnexionInterface $serviceConnexion
     * @param ServiceCarteInterface $serviceCarte
     * @param ServiceColonneInterface $serviceColonne
     *
     * fonction qui permet de construire le controleur de carte avec l'API
     */

    public function __construct(
        private ServiceUtilisateurInterface $serviceUtilisateur,
        private ServiceConnexionInterface   $serviceConnexion,
        private ServiceCarteInterface       $serviceCarte,
        private ServiceColonneInterface     $serviceColonne,
        private ConnexionUtilisateurInterface $connexionUtilisateur
    )
    {
    }


    /**
     * @param Request $request
     * @return Response
     *
     * fonction qui permet de supprimer une carte avec l'API
     */

    #[Route("/api/carte/supprimer", name: "supprimerCarteAPI", methods: "DELETE")]
    public function supprimerCarte(Request $request): Response
    {
        $jsondecode = json_decode($request->getContent());
        $idCarte = $jsondecode->idCarte;
        try {
            $this->serviceConnexion->pasConnecter();
            $carte = $this->serviceCarte->recupererCarte($idCarte);
            $tableau = $carte->getColonne()->getTableau();
            $this->serviceUtilisateur->estParticipant($tableau);
            $this->serviceCarte->supprimerCarte($tableau, $idCarte);
            return new JsonResponse('', 200);
        } catch (ServiceException $e) {
            return new JsonResponse(["error" => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * @param Request $request
     * @return Response
     *
     * fonction qui permet de modifier une carte avec l'API
     */
    #[Route("/api/carte/modifier", name: "modifierCarteAPI", methods: "PATCH")]
    public function modifierCarte(Request $request): Response
    {
        $jsondecode = json_decode($request->getContent());
        $idCarte = $jsondecode->idCarte ?? null;
        $idColonne = $jdondecode->idColonne ?? null;
        $attributs = [
            "titreCarte" => $jdondecode->titreCarte ?? null,
            "descriptifCarte" => $jdondecode->descriptifCarte ?? null,
            "couleurCarte" => $jdondecode->couleurCarte ?? null,
            "affectationsCarte" => $jdondecode->affectationsCarte ?? null,
        ];
        try {
            $this->serviceConnexion->pasConnecter();
            $colonne = $this->serviceColonne->recupererColonne($idColonne);
            $carte = $this->serviceCarte->verificationsMiseAJourCarte($idCarte, $colonne, $attributs);
            $tableau = $colonne->getTableau();
            $this->serviceUtilisateur->estParticipant($tableau);
            $carte = $this->serviceCarte->miseAJourCarte($tableau, $attributs, $carte, $colonne);
            return new JsonResponse($carte, 200);
        } catch (ServiceException $e) {
            return new JsonResponse(["error" => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * @param Request $request
     * @return Response
     *
     * fonction qui permet de creer une carte avec l'API
     */

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
            $this->serviceUtilisateur->estParticipant($tableau);
            $carte = $this->serviceCarte->creerCarte($tableau, $attributs, $colonne);
            return new JsonResponse($carte, 200);
        } catch (ServiceException $e) {
            return new JsonResponse(["error" => $e->getMessage()], $e->getCode());
        }
    }


    /**
     * @param Request $request
     * @return Response
     *
     * fonction qui permet de recuperer l'ID d'une carte avec l'API
     */
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
            $carte->setColonne($colonne);
            $this->serviceCarte->deplacerCarte($carte,$colonne);
            return new JsonResponse('',200);
        } catch (ServiceException $e) {
            return new JsonResponse(["error" => $e->getMessage()], $e->getCode());
        }
    }

}