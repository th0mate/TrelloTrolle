<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use Exception;

class TableauRepository extends AbstractRepository
{

    protected function getNomTable(): string
    {
        return "tableau";
    }

    protected function getNomCle(): string
    {
        return "idtableau";
    }

    protected function getNomsColonnes(): array
    {
        return ["idtableau", "codetableau", "titretableau", "login"];
    }

    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        return Tableau::construireDepuisTableau($objetFormatTableau);
    }

    public function recupererTableauxUtilisateur(string $login): array
    {
        return $this->recupererPlusieursPar("login", $login);
    }

    public function recupererParCodeTableau(string $codeTableau): ?AbstractDataObject
    {
        return $this->recupererPar("codetableau", $codeTableau);
    }


    /**
     * @return Tableau[]
     */
    public function recupererTableauxOuUtilisateurEstMembre(string $login): array
    {
        $sql = "select * from {$this->getNomTable()} t where {$this->getNomCle()} in 
                (select idtableau from participant p where p.login=:login) 
                or t.login=:login";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        //TODO encode avec JSON comme code de base
        $pdoStatement->execute(["login" => $login]);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }
        return $objets;
    }

    /**
     * @return Tableau[]
     */
    public function recupererTableauxParticipeUtilisateur(string $login): array
    {
        $sql = "SELECT DISTINCT {$this->formatNomsColonnes()}
                from {$this->getNomTable()} t JOIN participant p ON t.idtableau = p.idtableau
                WHERE p.idlogin = :login";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        //TODO encode avec JSON comme code de base
        $pdoStatement->execute(["login" => $login]);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }
        return $objets;
    }

    public function getNextIdTableau(): int
    {
        return $this->getNextId("idtableau");
    }

    public function getNombreTableauxTotalUtilisateur(string $login): int
    {
        $query = "SELECT COUNT(DISTINCT idtableau) FROM {$this->getNomTable()} WHERE login=:login";
        $pdoStatement =$this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["login" => $login]);
        $obj = $pdoStatement->fetch();
        return $obj[0];
    }



    public function participants(): array
    {
        $query = "SELECT login FROM particpant WHERE 
        idtableau = {$this->getNomCle()}";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($query);
        $pdoStatement->execute();
        $obj = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $obj[] = $objetFormatTableau;
        }
        return $obj;
    }

    public function estParticipant(string $login): bool
    {
        if (in_array($login, $this->participants(), true)) {
            return true;
        }
        return false;
    }

    public function estProprietaire($login): bool
    {
        $query = "SELECT login FROM {$this->getNomTable()} WHERE 
        idtableau = {$this->getNomCle()}";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($query);
        $pdoStatement->execute();
        $obj = $pdoStatement->fetch();
        if ($obj[0] === $login) {
            return true;
        } else {
            return false;
        }
    }

    public function estParticipantOuProprietaire(string $login): bool
    {
        return $this->estProprietaire($login) || $this->estParticipant($login);
    }

    public function getUtilisateur(Tableau  $idcle): Utilisateur
    {
        $formatNomsColonnes=(new UtilisateurRepository())->formatNomsColonnes();
        $query = "SELECT $formatNomsColonnes
        FROM {$this->getNomTable()} t JOIN utilisateur u
        ON u.idlogin=t.idlogin WHERE idtableau =: idcle";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($query);
        $pdoStatement->execute(["idcle" => $idcle->getIdTableau()]);
        $obj = $pdoStatement->fetch();
        return Utilisateur::construireDepuisTableau($obj);
    }

    public function getParticipants(Tableau $idcle): ?array
    {
        $query = "SELECT u.login,nom,prenom,email,mdphache
        FROM {$this->getNomTable()} c JOIN participant p
        ON c.idtableau=a.idtableau
        JOIN utilisateur u ON u.login=p.login WHERE a.{$this->getNomCle()} =:idcle";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($query);
        $pdoStatement->execute(["idcle" => $idcle->getIdTableau()]);
        $obj = [];
        foreach($pdoStatement as $objetFormatTableau) {
            $obj[] = Utilisateur::construireDepuisTableau($objetFormatTableau);
        }
        return $obj;
    }

    public static function setParticipants(?array $participants, Tableau $instance): void
    {
        foreach($participants as $participant) {
            $query = "INSERT INTO participant VALUES (:idtableau, :login)";
            $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($query);
            $pdoStatement->execute(["idtableau" => $instance->getIdTableau(), "login" => $participant->getLogin()]);
        }
    }

}