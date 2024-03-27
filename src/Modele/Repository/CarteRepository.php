<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use Exception;

class CarteRepository extends AbstractRepository implements CarteRepositoryInterface
{

    protected function getNomTable(): string
    {
        return "carte";
    }

    protected function getNomCle(): string
    {
        return "idcarte";
    }

    protected function getNomsColonnes(): array
    {
        return [
            "idcarte", "titrecarte", "descriptifcarte", "couleurcarte", "idcolonne"
        ];
    }

    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        return Carte::construireDepuisTableau($objetFormatTableau);
    }

    public function recupererCartesColonne(int $idcolonne): array {
        return $this->recupererPlusieursPar("idcolonne", $idcolonne);
    }

    public function recupererCartesTableau(int $idTableau): array {
        $sql = "SELECT c.idcarte, titrecarte, descriptifcarte, couleurcarte, c.idcolonne
        FROM {$this->getNomTable()} c 
        JOIN colonne co ON c.idcolonne=co.idcolonne
        WHERE co.idtableau=:idTableau";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["idTableau" => $idTableau]);
        $obj = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $obj[] = $this->construireDepuisTableau($objetFormatTableau);
        }
        return $obj;
    }

    /**
     * @return Carte[]
     */
    public function recupererCartesUtilisateur(string $login): array
    {
        $sql = "SELECT {$this->formatNomsColonnes()} from {$this->getNomTable()} c
        JOIN affectationcarte a ON a.idcarte=c.idcarte
        WHERE login=:login";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["login" => $login]);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }
        return $objets;
    }

    public function getNombreCartesTotalUtilisateur(string $login) : int {
        $query = "SELECT COUNT(*) FROM {$this->getNomTable()} WHERE login=:login";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["login" => $login]);
        $obj = $pdoStatement->fetch();
        return $obj[0];
    }

    public function getNextIdCarte() : int {
        return $this->getNextId("idcarte");
    }

    public function getAffectationsCarte(Carte $carte): ?array
    {
        $query = "SELECT u.login,nom,prenom,email,mdphache
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

    public function getAllFromCartes(int $idCarte): array
    {
        $query = "SELECT * FROM {$this->getNomTable()} ca 
        JOIN colonne co ON ca.idcolonne=co.idcolonne
        JOIN tableau ta ON co.idtableau=ta.idtableau
        JOIN utilisateur u ON ta.login=u.login
        WHERE idcarte=:idcarte";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["idcarte" => $idCarte]);
        $obj = [];
        foreach($pdoStatement as $objetFormatTableau) {
            $obj[] = $objetFormatTableau;
        }
        return $obj;
    }
}