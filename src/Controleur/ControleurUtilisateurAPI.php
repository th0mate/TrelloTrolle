<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateurInterface;
use App\Trellotrolle\Service\Exception\ConnexionException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\ServiceConnexionInterface;
use App\Trellotrolle\Service\ServiceTableauInterface;
use App\Trellotrolle\Service\ServiceColonneInterface;
use App\Trellotrolle\Service\ServiceConnexion;
use App\Trellotrolle\Service\ServiceUtilisateur;
use App\Trellotrolle\Service\ServiceUtilisateurInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ControleurUtilisateurAPI
{

    /**
     * ControleurUtilisateurAPI constructor.
     * @param ServiceUtilisateurInterface $serviceUtilisateur Le service utilisateur
     *
     * fonction qui permet de construire le controleur de l'utilisateur avec l'API
     */
    public function __construct(private ServiceUtilisateurInterface   $serviceUtilisateur,
                                private ConnexionUtilisateurInterface $connexionUtilisateur,
                                private ServiceColonneInterface $serviceColonne
    )
   {
    }

    /**
     * @param Request $request la requête
     * @return Response La réponse JSON
     *
     * fonction qui permet de rechercher un utilisateur avec l'API via une requête de recherche
     */
    #[Route("/api/utilisateur/recherche", name: "rechercheUtilisateurAPI", methods: "POST")]
    public function rechercheUtilisateur(Request $request): Response
    {
        $corps = $request->getContent();
        $jsondecode = json_decode($corps);
        $recherche = $jsondecode->recherche ?? null;
        try {
            $this->connexionUtilisateur->estConnecte();
            $resultats = $this->serviceUtilisateur->rechercheUtilisateur($recherche);
            return new JsonResponse($resultats, 200);
        } catch (ServiceException $e) {
            return new JsonResponse(["error" => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * @param Request $request la requête
     * @return Response La réponse JSON
     *
     * fonction qui permet de recuperer les affectations des colonnes avec l'API
     */
    #[Route('/api/utilisateur/affectations',name: "affectationsColonnesAPI",methods: "POST")]
    public function getAffectationsColonnes(Request $request):Response
    {
        $jsondecode=json_decode($request->getContent());
        $idColonne=$jsondecode->idColonne ??null;
        $login=$jsondecode->login ??null;
        try{
            $colonne=$this->serviceColonne->recupererColonne($idColonne);
            $affectationsColonnes=$this->serviceUtilisateur->recupererAffectationsColonne($colonne,$login);
            return new JsonResponse($affectationsColonnes,200);
        }catch (ServiceException $e){
            return new JsonResponse(["error"=>$e->getMessage()],$e->getCode());
        }
    }
}