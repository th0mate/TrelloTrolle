<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Service\Exception\ConnexionException;
use App\Trellotrolle\Service\Exception\CreationException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;
use App\Trellotrolle\Service\ServiceColonne;
use App\Trellotrolle\Service\ServiceConnexion;
use App\Trellotrolle\Service\ServiceTableau;
use App\Trellotrolle\Service\ServiceUtilisateur;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ControleurColonneAPI
{

    public function __construct(
        private ServiceConnexion $serviceConnexion,
        private ServiceColonne $serviceColonne,
        private ServiceUtilisateur $serviceUtilisateur,
        private ServiceTableau $serviceTableau
    )
    {
    }

    #[Route("/api/colonne/creer",name: "creerColonneAPI",methods: "PUT")]
    public function creerColonne():Response
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
            return new JsonResponse($colonne,200);
        }catch (ServiceException $e) {
            return new JsonResponse(["error"=>$e->getMessage()],$e->getCode());
        }
    }

    #[Route("/api/colonne/supprimer/{idColonne}",name: "supprimerColonneAPI",methods: "DELETE")]
    public function supprimerColonne($idColonne): Response
    {
        try {
            $this->serviceConnexion->pasConnecter();
            $colonne = $this->serviceColonne->recupererColonne($idColonne);
            $tableau = $colonne->getTableau();
            $this->serviceUtilisateur->estParticipant($tableau);
            $this->serviceColonne->supprimerColonne($tableau, $idColonne);
            return new JsonResponse('', 200);
        } catch (ServiceException $e) {
            return new JsonResponse(["error" => $e->getMessage()], $e->getCode());
        }
    }

    #[Route("/api/colonne/modifier/{idColonne}",name: "modifierColonneAPI",methods:"PATCH" )]
    public function modifierColonne($idColonne):Response
    {
        $nomColonne = $_REQUEST["nomColonne"] ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $colonne = $this->serviceColonne->recupererColonneAndNomColonne($idColonne, $nomColonne);
            $tableau = $colonne->getTableau();
            $this->serviceUtilisateur->estParticipant($tableau);
            $colonne->setTitreColonne($nomColonne);
            $colonne=$this->serviceColonne->miseAJourColonne($colonne);
            return new JsonResponse($colonne,200);
        }  catch (ServiceException $e) {
            return new JsonResponse(["error"=>$e->getMessage()],$e->getCode());
        }
    }
    #[Route("/api/colonne/nextid",name: "getNextIdColonneAPI",methods: "POST")]
    public function getNextIdColonne():Response
    {
        $idColonne=$this->serviceColonne->getNextIdColonne();
        return new JsonResponse(["idColonne"=>$idColonne],200);
    }
}