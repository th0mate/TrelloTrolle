<?php

namespace App\Trellotrolle\Controleur;

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
use Symfony\Component\Routing\Annotation\Route;

class ControleurTableauAPI
{

    public function __construct(
        private ServiceConnexionInterface   $serviceConnexion,
        private ServiceTableauInterface     $serviceTableau,
        private ServiceUtilisateurInterface $serviceUtilisateur,
        private ServiceCarteInterface       $serviceCarte
    )
    {
    }

    #[Route("/api/tableau/membre/ajouter", name: "ajouterMembreAPI", methods: "PATCH")]
    public function ajouterMembre()
    {
        {
            $idTableau = $_REQUEST["idTableau"] ?? null;
            $login = $_REQUEST["login"] ?? null;
            try {
                $this->serviceConnexion->pasConnecter();
                $tableau = $this->serviceTableau->recupererTableauParId($idTableau);
                $this->serviceUtilisateur->ajouterMembre($tableau, $login);
                return new JsonResponse('', 200);
            } catch (ServiceException $e) {
                return new JsonResponse(["error" => $e->getMessage()], $e->getCode());
            }
        }
    }

    #[Route('/api/tableau/membre/supprimer', name: 'supprimerMembreAPI', methods: "PATCH")]
    public function supprimerMembre()
    {
        $idTableau = $_REQUEST["idTableau"] ?? null;
        $login = $_REQUEST["login"] ?? null;
        try {
            $this->serviceConnexion->pasConnecter();
            $tableau = $this->serviceTableau->recupererTableauParId($idTableau);
            $utilisateur = $this->serviceUtilisateur->supprimerMembre($tableau, $login);
            $this->serviceCarte->miseAJourCarteMembre($tableau, $utilisateur);
            return new JsonResponse("", 200);

        } catch (ServiceException $e) {
            return new JsonResponse(["error" => $e->getMessage()], $e->getCode());
        }
    }
}