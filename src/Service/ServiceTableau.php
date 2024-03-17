<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Controleur\ControleurCarte;
use App\Trellotrolle\Controleur\ControleurColonne;
use App\Trellotrolle\Controleur\ControleurTableau;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;

class ServiceTableau
{

    private TableauRepository $tableauRepository;
    private ColonneRepository $colonneRepository;
    private CarteRepository $carteRepository;
    public function __construct()
    {
        $this->tableauRepository=new TableauRepository();
        $this->colonneRepository=new ColonneRepository();
        $this->carteRepository = new CarteRepository();
    }

    /**
     * @throws ServiceException
     */
    public function recupererTableauParId($idTableau):Tableau
    {
        if (is_null($idTableau)) {
            throw new ServiceException("Identifiant du tableau manquant");
        }
        /**
         * @var Tableau $tableau
         */
        $tableau = $this->tableauRepository->recupererParClePrimaire($idTableau);
        if (!$tableau) {
            throw new ServiceException("Tableau inexistant");
        }
        return $tableau;
    }

    /**
     * @throws ServiceException
     */
    public function recupererTableauParCode($codeTableau): Tableau
    {
        if(is_null($codeTableau)) {
            throw new ServiceException("Code de tableau manquant");
        }
        /**
         * @var Tableau $tableau
         */
        $tableau = $this->tableauRepository->recupererParCodeTableau($codeTableau);
        if(!$tableau) {
            throw new ServiceException("Tableau inexistant");
        }
        return $tableau;

    }

    public function recupererCartesColonnes($tableau): array
    {
        /**
         * @var Colonne[] $colonnes
         */
        $colonnes = $this->colonneRepository->recupererColonnesTableau($tableau->getIdTableau());
        $data = [];
        $participants = [];

        foreach ($colonnes as $colonne) {
            /**
             * @var Carte[] $cartes
             */
            $cartes = $this->carteRepository->recupererCartesColonne($colonne->getIdColonne());
            foreach ($cartes as $carte) {
                foreach ($carte->getAffectationsCarte() as $utilisateur) {
                    if(!isset($participants[$utilisateur->getLogin()])) {
                        $participants[$utilisateur->getLogin()] = ["infos" => $utilisateur, "colonnes" => []];
                    }
                    if(!isset($participants[$utilisateur->getLogin()]["colonnes"][$colonne->getIdColonne()])) {
                        $participants[$utilisateur->getLogin()]["colonnes"][$colonne->getIdColonne()] = [$colonne->getTitreColonne(), 0];
                    }
                    $participants[$utilisateur->getLogin()]["colonnes"][$colonne->getIdColonne()][1]++;
                }
            }
            $data[] = $cartes;
        }
        return ["data"=>$data,"colonnes"=>$colonnes,"participants"=>$participants];
    }

    public function recupererTableauEstMembre($login)
    {
        return $this->tableauRepository->recupererTableauxOuUtilisateurEstMembre($login);
    }

    /**
     * @throws TableauException
     */
    public function isNotNullNomTableau($nomTableau, $tableau)
    {
        if (is_null($nomTableau)){
            throw new TableauException("Nom de tableau manquant",$tableau);
        }
    }

    public function mettreAJourTableau($tableau)
    {
        $this->tableauRepository->mettreAJour($tableau);
    }

}