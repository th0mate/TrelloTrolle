<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\ConnexionUtilisateurInterface;
use App\Trellotrolle\Service\Exception\ServiceException;
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
     * @param ServiceUtilisateurInterface $serviceUtilisateur
     *
     * fonction qui permet de construire le controleur de l'utilisateur avec l'API
     */
    public function __construct(private ServiceUtilisateurInterface   $serviceUtilisateur,
                                private ConnexionUtilisateurInterface $connexionUtilisateur
    )
   {
    }

    /**
     * @param Request $request
     * @return Response
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
            $resultats = $this->serviceUtilisateur->rechercheUtilisateur($recherche);
            return new JsonResponse($resultats, 200);
        } catch (ServiceException $e) {
            return new JsonResponse(["error" => $e->getMessage()], $e->getCode());
        }
    }
}