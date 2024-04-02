<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Controleur\ControleurCarte;
use App\Trellotrolle\Controleur\ControleurColonne;
use App\Trellotrolle\Controleur\ControleurTableau;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
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


    /**
     * ServiceTableau constructor.
     * @param TableauRepositoryInterface $tableauRepository Repository des tableaux
     * @param ColonneRepositoryInterface $colonneRepository Repository des colonnes
     * @param CarteRepositoryInterface $carteRepository Repository des cartes
     * @param UtilisateurRepositoryInterface $utilisateurRepository Repository des utilisateurs
     */
    public function __construct(private TableauRepositoryInterface     $tableauRepository,
                                private ColonneRepositoryInterface     $colonneRepository,
                                private CarteRepositoryInterface       $carteRepository,
                                private UtilisateurRepositoryInterface $utilisateurRepository)
    {
    }


    /**
     * Fonction permettant de récupérer un tableau par son id
     * @param $idTableau L'id du tableau à récupérer
     * @return Tableau Le tableau récupéré
     * @throws ServiceException Si l'identifiant du tableau est manquant
     * ou si le tableau est inexistant
     */
    public function recupererTableauParId($idTableau): Tableau
    {
        if (is_null($idTableau)) {
            throw new ServiceException("Identifiant du tableau manquant", 404);
        }
        /**
         * @var Tableau $tableau
         */
        $tableau = $this->tableauRepository->recupererParClePrimaire($idTableau);
        if (!$tableau) {
            throw new ServiceException("Tableau inexistant", 404);
        }
        return $tableau;
    }


    /**
     * Fonction permettant de récupérer un tableau par son code
     * @param $codeTableau, Le code du tableau à récupérer
     * @return Tableau Le tableau récupéré
     * @throws ServiceException Si le code du tableau est manquant ou si le tableau est inexistant
     */
    public function recupererTableauParCode($codeTableau): Tableau
    {
        if (is_null($codeTableau)) {
            throw new ServiceException("Code de tableau manquant", 404);
        }
        /**
         * @var Tableau $tableau
         */
        $tableau = $this->tableauRepository->recupererParCodeTableau($codeTableau);
        if (!$tableau) {
            throw new ServiceException("Tableau inexistant", 404);
        }
        return $tableau;

    }

    /**
     * Fonction permettant de récupérer les cartes des colonnes d'un tableau
     * @param Tableau $tableau Le tableau dont on veut récupérer les cartes
     * @return array Les cartes des colonnes du tableau
     */
    public function recupererCartesColonnes(Tableau $tableau): array
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
                $utilisateurs = $this->carteRepository->getAffectationsCarte($carte);
                $participants[$carte->getIdCarte()] = $utilisateurs;
            }
            $data[] = $cartes;
        }
        return ["data" => $data, "colonnes" => $colonnes, "participants" => $participants];
    }

    /**
     * Fonction permettant de récupérer les tableaux où un utilisateur est membre
     * @param $login, Le login de l'utilisateur
     * @return array Les tableaux où l'utilisateur est présent
     */
    public function recupererTableauEstMembre($login): array
    {
        return $this->tableauRepository->recupererTableauxOuUtilisateurEstMembre($login);
    }


    /**
     * Fonction permettant de vérifier si un tableau est null via son nom
     * @param $nomTableau, Le nom du tableau à vérifier
     * @param Tableau $tableau Le tableau à vérifier
     * @return void
     * @throws TableauException Si le nom du tableau est manquant
     */
    public function isNotNullNomTableau($nomTableau, Tableau $tableau): void
    {
        if (is_null($nomTableau)) {
            throw new TableauException("Nom de tableau manquant", $tableau, 404);
        }
    }

    /**
     * Fonction permettant de mettre à jour un tableau
     * @param Tableau $tableau Le tableau à mettre à jour
     * @return void
     */
    public function mettreAJourTableau(Tableau $tableau): void
    {
        $this->tableauRepository->mettreAJour($tableau);
    }


    /**
     * Fonction permettant de supprimer un tableau
     * @param $idTableau L'id du tableau à supprimer
     * @return void
     */
    public function supprimerTableau($idTableau): void
    {
        $this->tableauRepository->supprimer($idTableau);
    }


    /**
     * Fonction permettant de quitter un tableau
     * @param Tableau $tableau Le tableau à quitter
     * @param AbstractDataObject $utilisateur L'utilisateur qui quitte le tableau
     * @return void
     * @throws ServiceException Si l'utilisateur n'appartient pas au tableau ou
     * s'il est propriétaire du tableau
     */
    public function quitterTableau(Tableau $tableau, AbstractDataObject $utilisateur): void
    {
        if ($this->tableauRepository->estProprietaire($utilisateur->getLogin(), $tableau)) {;
            throw new ServiceException("Vous ne pouvez pas quitter ce tableau",Response::HTTP_FORBIDDEN);
        }
        if (!$this->tableauRepository->estParticipant($utilisateur->getLogin(), $tableau)) {
            throw new ServiceException("Vous n'appartenez pas à ce tableau",Response::HTTP_FORBIDDEN);
        }

        $participants = array_filter($this->tableauRepository->getParticipants($tableau), function ($u) use ($utilisateur) {
            return $u->getLogin() !== $utilisateur->getLogin();
        });
        $this->tableauRepository->setParticipants($participants, $tableau);

        $cartes = $this->carteRepository->recupererCartesTableau($tableau->getIdTableau());
        foreach ($cartes as $carte) {
            $affectations = array_filter($this->carteRepository->getAffectationsCarte($carte), function ($u) use ($utilisateur) {
                return $u->getLogin() != $utilisateur->getLogin();
            });
            $this->carteRepository->setAffectationsCarte($affectations, $carte);
        }
    }


    /**
     * Fonction permettant de créer un tableau
     * @param $nomTableau, Le nom du tableau à créer
     * @param $login, Le login de l'utilisateur qui crée le tableau
     * @return Tableau Le tableau créé
     * @throws ServiceException Si le nom du tableau est manquant
     */
    public function creerTableau($nomTableau, $login)
    {
        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($login);
        if (is_null($nomTableau)) {
            throw new ServiceException("Nom de tableau manquant", 404);
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

    /**
     * Fonction permettant de vérifier si un utilisateur est participant à un tableau
     * @param Tableau $tableau Le tableau sur lequel on veut vérifier si
     * l'utilisateur est participant
     * @param $login, Le login de l'utilisateur à vérifier
     * @return bool Vrai si l'utilisateur est participant, faux sinon
     */
    public function estParticipant(Tableau $tableau, $login): bool
    {
        return $this->tableauRepository->estParticipantOuProprietaire($login, $tableau);
    }
}