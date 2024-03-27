<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Controleur\ControleurCarte;
use App\Trellotrolle\Controleur\ControleurColonne;
use App\Trellotrolle\Controleur\ControleurTableau;
use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\CarteRepositoryInterface;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepositoryInterface;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Modele\Repository\TableauRepositoryInterface;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepositoryInterface;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;
use http\Message;
use Symfony\Component\HttpFoundation\Response;

class ServiceTableau implements ServiceTableauInterface
{


    public function __construct(private TableauRepositoryInterface     $tableauRepository,
                                private ColonneRepositoryInterface     $colonneRepository,
                                private CarteRepositoryInterface       $carteRepository,
                                private UtilisateurRepositoryInterface $utilisateurRepository)
    {
    }

    /**
     * @throws ServiceException
     */
    public function recupererTableauParId($idTableau): Tableau
    {
        if (is_null($idTableau)) {
            throw new ServiceException("Identifiant du tableau manquant",404);
        }
        /**
         * @var Tableau $tableau
         */
        $tableau = $this->tableauRepository->recupererParClePrimaire($idTableau);
        if (!$tableau) {
            throw new ServiceException("Tableau inexistant",404);
        }
        return $tableau;
    }

    /**
     * @throws ServiceException
     */
    public function recupererTableauParCode($codeTableau): Tableau
    {
        if (is_null($codeTableau)) {
            throw new ServiceException("Code de tableau manquant",404);
        }
        /**
         * @var Tableau $tableau
         */
        $tableau = $this->tableauRepository->recupererParCodeTableau($codeTableau);
        if (!$tableau) {
            throw new ServiceException("Tableau inexistant",404);
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
                foreach ($this->carteRepository->getAffectationsCarte($carte) as $utilisateur) {
                    if (!isset($participants[$utilisateur->getLogin()])) {
                        $participants[$utilisateur->getLogin()] = ["infos" => $utilisateur, "colonnes" => []];
                    }
                    if (!isset($participants[$utilisateur->getLogin()]["colonnes"][$colonne->getIdColonne()])) {
                        $participants[$utilisateur->getLogin()]["colonnes"][$colonne->getIdColonne()] = [$colonne->getTitreColonne(), 0];
                    }
                    $participants[$utilisateur->getLogin()]["colonnes"][$colonne->getIdColonne()][1]++;
                }
            }
            $data[] = $cartes;
        }
        return ["data" => $data, "colonnes" => $colonnes, "participants" => $participants];
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
        if (is_null($nomTableau)) {
            throw new TableauException("Nom de tableau manquant", $tableau,404);
        }
    }

    public function mettreAJourTableau($tableau)
    {
        $this->tableauRepository->mettreAJour($tableau);
    }

    public function supprimerTableau($idTableau)
    {
        $this->tableauRepository->supprimer($idTableau);
    }

    /**
     * @throws ServiceException
     */
    public function quitterTableau($tableau, $utilisateur)
    {
        if ($tableau->estProprietaire($utilisateur->getLogin())) {
            throw new ServiceException("Vous ne pouvez pas quitter ce tableau",Response::HTTP_FORBIDDEN);
        }
        if (!$tableau->estParticipant(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            throw new ServiceException("Vous n'appartenez pas à ce tableau",Response::HTTP_UNAUTHORIZED);
        }
        $participants = array_filter($tableau->getParticipants(), function ($u) use ($utilisateur) {
            return $u->getLogin() !== $utilisateur->getLogin();
        });
        $tableau->setParticipants($participants);
        $this->tableauRepository->mettreAJour($tableau);

        $cartes = $this->carteRepository->recupererCartesTableau($tableau->getIdTableau());
        foreach ($cartes as $carte) {
            $affectations = array_filter($carte->getAffectationsCarte(), function ($u) use ($utilisateur) {
                return $u->getLogin() != $utilisateur->getLogin();
            });
            $carte->setAffectationsCarte($affectations);
            $this->carteRepository->mettreAJour($carte);
        }
    }

    /**
     * @throws ServiceException
     */
    public function creerTableau($nomTableau,$login)
    {
        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($login);
        if (is_null($nomTableau)) {
            throw new ServiceException("Nom de tableau manquant",404);
        }
        $idTableau = $this->tableauRepository->getNextIdTableau();
        $codeTableau = hash("sha256", $utilisateur->getLogin() . $idTableau);

        $idColonne1 = $this->colonneRepository->getNextIdColonne();
        $nomColonne1 = "TODO";

        $carteInitiale = "Exemple";
        $descriptifInitial = "Exemple de carte";

        $idCarte1 = $this->carteRepository->getNextIdCarte();

        $tableau = new Tableau(
            $idTableau,
            $codeTableau,
            $nomTableau,
            $utilisateur
        );

        $colonne = new Colonne(
            $idColonne1,
            $nomColonne1,
            $tableau
        );

        $carte1 = new Carte(
            $idCarte1,
            $carteInitiale,
            $descriptifInitial,
            "#FFFFFF",
            $colonne
        );

        $this->tableauRepository->ajouter($tableau);
        $this->colonneRepository->ajouter($colonne);
        $this->carteRepository->ajouter($carte1);
        return $tableau;
    }

    public function estParticipant($login)
    {
        return $this->tableauRepository->estParticipantOuProprietaire($login);
    }
}