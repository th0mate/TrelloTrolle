<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use Exception;

class CarteRepository extends AbstractRepository
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
        return $this->recupererPlusieursPar("idtableau", $idTableau);
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

    public function getAffectationsCarte(Carte  $idcle): ?array
    {
        $query = "SELECT u.login,nom,prenom,email,mdphache
        FROM {$this->getNomTable()} c JOIN affectationcarte a
        ON c.idcarte=a.idcarte
        JOIN utilisateur u ON u.login=a.login 
        WHERE a.{$this->getNomCle()} =:idcle";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["idcle" => $idcle->getIdCarte()]);
        $obj = [];
        foreach($pdoStatement as $objetFormatTableau) {
            $obj[] = Utilisateur::construireDepuisTableau($objetFormatTableau);
        }
        return $obj;
    }

    public function setAffectationsCarte(?array $affectationsCarte, Carte $instance): void
    {
        foreach($affectationsCarte as $affectationCarte) {
            $query = "INSERT INTO affectationcarte VALUES (:idcarte, :login)";
            $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
            $pdoStatement->execute(["idcarte" => $instance->getIdCarte(), "login" => $affectationCarte->getLogin()]);
        }
    }
}