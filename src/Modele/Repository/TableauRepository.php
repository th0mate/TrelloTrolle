<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use Exception;

class TableauRepository extends AbstractRepository implements TableauRepositoryInterface
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

    protected function construireDepuisTableau(array $objetFormatTableau): Tableau
    {
        return Tableau::construireDepuisTableau($objetFormatTableau);
    }

    public function recupererTableauxUtilisateur(string $login): array
    {
        return $this->recupererPlusieursPar("login", $login);
    }

    public function recupererParCodeTableau(string $codeTableau): ?AbstractDataObject
    {
        $query = "SELECT idtableau,codetableau,titretableau,t.login, nom,prenom,email,mdphache FROM {$this->getNomTable()} t
        JOIN utilisateur u ON t.login=u.login
        WHERE codetableau=:codetableau";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["codetableau" => $codeTableau]);
        $objetFormatTableau = $pdoStatement->fetch();
        if ($objetFormatTableau === false) {
            return null;
        }
        return $this->construireDepuisTableau($objetFormatTableau);
    }


    /**
     * @return Tableau[]
     */
    public function recupererTableauxOuUtilisateurEstMembre(string $login): array
    {
        $sql = "select idtableau from {$this->getNomTable()} t where {$this->getNomCle()} in 
                (select idtableau from participant p where p.login=:login) 
                or t.login=:login";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["login" => $login]);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->getAllFromTableau($objetFormatTableau);
        }
        return $objets;
    }

    /**
     * @return Tableau[]
     */
    public function recupererTableauxParticipeUtilisateur(string $login): array
    {
        $sql = "SELECT DISTINCT p.idtableau, codetableau, titretableau, t.login
                from {$this->getNomTable()} t JOIN participant p ON t.idtableau = p.idtableau
                WHERE p.login = :login";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
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


    public function estParticipant(string $login, Tableau $tableau): bool
    {
        for ($i = 0; $i < count($this->getParticipants($tableau)); $i++) {
            if ($this->getParticipants($tableau)[$i]->getLogin() === $login) {
                return true;
            }
        }
        return false;
    }

    public function estProprietaire($login, Tableau $tableau): bool
    {
        $query = "SELECT login FROM {$this->getNomTable()} WHERE 
        {$this->getNomCle()} =:idtableau";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["idtableau" => $tableau->getIdTableau()]);
        $obj = $pdoStatement->fetch();
        if ($obj[0] === $login) {
            return true;
        } else {
            return false;
        }
    }

    public function estParticipantOuProprietaire(string $login, Tableau $tableau): bool
    {
        return $this->estProprietaire($login, $tableau) || $this->estParticipant($login, $tableau);
    }

    public function getParticipants(Tableau $tableau): ?array
    {
        $query = "SELECT u.login,nom,prenom,email,mdphache
        FROM {$this->getNomTable()} t 
        JOIN participant p ON t.idtableau=p.idtableau
        JOIN utilisateur u ON u.login=p.login WHERE p.{$this->getNomCle()} =:idtableau";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["idtableau" => $tableau->getIdTableau()]);
        $obj = [];
        foreach($pdoStatement as $objetFormatTableau) {
            $obj[] = Utilisateur::construireDepuisTableau($objetFormatTableau);
        }
        return $obj;
    }

    public function setParticipants(?array $participants, Tableau $tableau): void
    {
        $query = "DELETE FROM participant WHERE idtableau=:idtableau";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["idtableau" => $tableau->getIdTableau()]);
        foreach($participants as $participant) {
            $query = "INSERT INTO participant VALUES (:login, :idtableau)";
            $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
            $pdoStatement->execute(["idtableau" => $tableau->getIdTableau(), "login" => $participant->getLogin()]);
        }
    }

    public function getProprietaire(Tableau $tableau) : Utilisateur
    {
        $query = "SELECT u.login,nom,prenom,email,mdphache
        FROM {$this->getNomTable()} t
        JOIN utilisateur u ON t.login=u.login
        WHERE t.{$this->getNomCle()}=:idtableau";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["idtableau" => $tableau->getIdTableau()]);
        $objetFormatTableau = $pdoStatement->fetch();
        return Utilisateur::construireDepuisTableau($objetFormatTableau);
    }

    public function getAllFromTableau(int $idTableau): Tableau
    {
        $query = "SELECT * FROM {$this->getNomTable()} ta
        JOIN utilisateur u ON ta.login=u.login
        WHERE idtableau=:idTableau";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["idTableau" => $idTableau]);
        return $this->construireDepuisTableau($pdoStatement->fetch());
    }

}