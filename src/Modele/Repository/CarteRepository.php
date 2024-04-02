<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use Exception;

class CarteRepository extends AbstractRepository implements CarteRepositoryInterface
{

    /**
     * Fonction permettant de récupérer le nom de la table
     * @return string Le nom de la table
     */
    protected function getNomTable(): string
    {
        return "carte";
    }

    /**
     * Fonction permettant de récupérer le nom de la clé primaire
     * @return string  Le nom de la clé primaire
     */
    protected function getNomCle(): string
    {
        return "idcarte";
    }

    /**
     * Fonction permettant de récupérer les noms des colonnes
     * @return string[] Les noms des colonnes
     */
    protected function getNomsColonnes(): array
    {
        return [
            "idcarte", "titrecarte", "descriptifcarte", "couleurcarte", "idcolonne"
        ];
    }

    /**
     * Fonction permettant de construire un objet depuis un tableau de paramètres
     * @param array $objetFormatTableau Le tableau de paramètres
     * @return Carte
     */
    protected function construireDepuisTableau(array $objetFormatTableau): Carte
    {
        return Carte::construireDepuisTableau($objetFormatTableau);
    }

    /**
     * Fonction permettant de récupérer toutes les cartes d'une colonne
     * @param int $idcolonne L'id de la colonne
     * @return array
     */
    public function recupererCartesColonne(int $idcolonne): array {
        return $this->recupererPlusieursPar("idcolonne", $idcolonne);
    }

    /**
     * Fonction permettant de récupérer toutes les cartes d'un tableau
     * @param int $idTableau L'id du tableau
     * @return array Les cartes récupérées
     */
    public function recupererCartesTableau(int $idTableau): array {
        $sql = "SELECT {$this->getNomCle()}
        FROM {$this->getNomTable()} c 
        JOIN colonne co ON c.idcolonne=co.idcolonne
        WHERE idtableau=:idTableau";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["idTableau" => $idTableau]);
        $obj = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $obj[] = $this->getAllFromTable($objetFormatTableau[$this->getNomCle()]);
        }
        return $obj;
    }

    /**
     * Fonction permettant de récupérer toutes les cartes d'un utilisateur
     * @return Carte[] Les cartes récupérées
     */
    public function recupererCartesUtilisateur(string $login): array
    {
        $sql = "SELECT c.idcarte
        from {$this->getNomTable()} c
        JOIN affectationcarte a ON a.idcarte=c.idcarte
        WHERE login=:login";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["login" => $login]);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->getAllFromTable($objetFormatTableau["idcarte"]);
        }
        return $objets;
    }

    /**
     * Fonction permettant de récupérer le nombre de cartes total d'un utilisateur
     * @param string $login Le login de l'utilisateur
     * @return int Le nombre de cartes de l'utilisateur
     */
    public function getNombreCartesTotalUtilisateur(string $login) : int {
        $query = "SELECT COUNT(*) FROM {$this->getNomTable()} c 
        JOIN affectationcarte a ON a.idcarte=c.idcarte
        WHERE login=:login";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["login" => $login]);
        $obj = $pdoStatement->fetch();
        return $obj[0];
    }

    /**
     *  Fonction permettant de récupérer l'id de la prochaine carte
     * @return int L'id de la prochaine carte
     */
    public function getNextIdCarte() : int {
        return $this->getNextId("idcarte");
    }

    /**
     * Fonction permettant de récupérer les affectations d'une carte
     * @param Carte $carte La carte
     * @return array|null Les affectations récupérées
     */
    public function getAffectationsCarte(Carte $carte): ?array
    {
        if(!$this->getAllFromTable($carte->getIdCarte())) {
            return null;
        }
        $query = "SELECT u.login, nom, prenom, email, mdphache
        FROM {$this->getNomTable()} c JOIN affectationcarte a
        ON c.idcarte=a.idcarte
        JOIN utilisateur u ON u.login=a.login 
        WHERE a.{$this->getNomCle()} =:idcarte";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["idcarte" => $carte->getIdCarte()]);
        $obj = [];
        foreach($pdoStatement as $objetFormatTableau) {
            $obj[] = Utilisateur::construireDepuisTableau($objetFormatTableau);
        }
        return $obj;
    }

    /**
     * Fonction permettant de mettre à jour les affectations d'une carte
     * @param array|null $affectationsCarte Les affectations
     * @param Carte $carte La carte
     * @return void
     */
    public function setAffectationsCarte(?array $affectationsCarte, Carte $carte): void
    {
        $query = "DELETE FROM affectationcarte WHERE idcarte=:idcarte";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["idcarte" => $carte->getIdCarte()]);
        foreach($affectationsCarte as $affectationCarte) {
            $query = "INSERT INTO affectationcarte VALUES (:idcarte, :login)";
            $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
            $pdoStatement->execute(["idcarte" => $carte->getIdCarte(), "login" => $affectationCarte->getLogin()]);
        }
    }

    /**
     * Fonction permettant de récupérer une carte en fonction de son id
     * @param int $idCarte L'id de la carte
     * @return Carte|null La carte récupérée
     */
    public function getAllFromTable(int|string $idCle): ?Carte
   {
        $query = "SELECT * FROM {$this->getNomTable()} ca 
        JOIN colonne co ON ca.idcolonne=co.idcolonne
        JOIN tableau ta ON co.idtableau=ta.idtableau
        JOIN utilisateur u ON ta.login=u.login
        WHERE idcarte=:idcarte";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["idcarte" => $idCle]);
        $objetFormatTableau = $pdoStatement->fetch();
        if (!$objetFormatTableau) {
            return null;
        }
        return $this->construireDepuisTableau($objetFormatTableau);
    }
}