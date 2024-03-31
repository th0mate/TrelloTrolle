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
     * @return string
     */
    protected function getNomTable(): string
    {
        return "carte";
    }

    /**
     * @return string
     */
    protected function getNomCle(): string
    {
        return "idcarte";
    }

    /**
     * @return string[]
     */
    protected function getNomsColonnes(): array
    {
        return [
            "idcarte", "titrecarte", "descriptifcarte", "couleurcarte", "idcolonne"
        ];
    }

    /**
     * @param array $objetFormatTableau
     * @return Carte
     */
    protected function construireDepuisTableau(array $objetFormatTableau): Carte
    {
        return Carte::construireDepuisTableau($objetFormatTableau);
    }

    /**
     * @param int $idcolonne
     * @return array
     */
    public function recupererCartesColonne(int $idcolonne): array {
        return $this->recupererPlusieursPar("idcolonne", $idcolonne);
    }

    /**
     * @param int $idTableau
     * @return array
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
     * @return Carte[]
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
     * @param string $login
     * @return int
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
     * @return int
     */
    public function getNextIdCarte() : int {
        return $this->getNextId("idcarte");
    }

    /**
     * @param Carte $carte
     * @return array|null
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
     * @param array|null $affectationsCarte
     * @param Carte $carte
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
     * @param int $idCarte
     * @return Carte
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