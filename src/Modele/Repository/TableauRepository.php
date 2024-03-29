<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use Exception;

class TableauRepository extends AbstractRepository implements TableauRepositoryInterface
{

    /**
     * @return string
     */
    protected function getNomTable(): string
    {
        return "tableau";
    }

    /**
     * @return string
     */
    protected function getNomCle(): string
    {
        return "idtableau";
    }

    /**
     * @return string[]
     */
    protected function getNomsColonnes(): array
    {
        return ["idtableau", "codetableau", "titretableau", "login"];
    }

    /**
     * @param array $objetFormatTableau
     * @return AbstractDataObject
     */
    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        return Tableau::construireDepuisTableau($objetFormatTableau);
    }

    /**
     * @param string $login
     * @return array
     */
    public function recupererTableauxUtilisateur(string $login): array
    {
        return $this->recupererPlusieursPar("login", $login);
    }

    /**
     * @param string $codeTableau
     * @return AbstractDataObject|null
     */
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
        $sql = "select * from {$this->getNomTable()} t where {$this->getNomCle()} in 
                (select idtableau from participant p where p.login=:login) 
                or t.login=:login";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
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
        $pdoStatement->execute(["login" => $login]);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }
        return $objets;
    }

    /**
     * @return int
     */
    public function getNextIdTableau(): int
    {
        return $this->getNextId("idtableau");
    }

    /**
     * @param string $login
     * @return int
     */
    public function getNombreTableauxTotalUtilisateur(string $login): int
    {
        $query = "SELECT COUNT(DISTINCT idtableau) FROM {$this->getNomTable()} WHERE login=:login";
        $pdoStatement =$this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["login" => $login]);
        $obj = $pdoStatement->fetch();
        return $obj[0];
    }


    /**
     * @param string $login
     * @param Tableau $tableau
     * @return bool
     */
    public function estParticipant(string $login, Tableau $tableau): bool
    {
        for ($i = 0; $i < count($this->getParticipants($tableau)); $i++) {
            if ($this->getParticipants($tableau)[$i]->getLogin() === $login) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $login
     * @param Tableau $tableau
     * @return bool
     */
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

    /**
     * @param string $login
     * @param Tableau $tableau
     * @return bool
     */
    public function estParticipantOuProprietaire(string $login, Tableau $tableau): bool
    {
        return $this->estProprietaire($login, $tableau) || $this->estParticipant($login, $tableau);
    }

    /**
     * @param Tableau $tableau
     * @return array|null
     */
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

    /**
     * @param array|null $participants
     * @param Tableau $tableau
     * @return void
     */
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

    /**
     * @param Tableau $tableau
     * @return Utilisateur
     */
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

    /**
     * @param int $idTableau
     * @return array
     */
    public function getAllFromTableau(int $idTableau): array
    {
        $query = "SELECT * FROM {$this->getNomTable()} ta
        JOIN utilisateur u ON ta.login=u.login
        WHERE idcarte=:idTableau";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["idTableau" => $idTableau]);
        $obj = [];
        foreach($pdoStatement as $objetFormatTableau) {
            $obj[] = $objetFormatTableau;
        }
        return $obj;
    }

}