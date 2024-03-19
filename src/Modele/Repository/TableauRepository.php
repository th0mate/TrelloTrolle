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
     * @throws Exception
     */
    public function ajouter(AbstractDataObject $object): bool
    {
        throw new Exception("Impossible d'ajouter seulement un tableau...");
    }

    /**
     * @return Tableau[]
     */
    public function recupererTableauxOuUtilisateurEstMembre(string $login): array
    {
        $sql = "SELECT DISTINCT {$this->formatNomsColonnes()}
                from {$this->getNomTable()} t JOIN participant p ON t.idtableau = p.idtableau
                WHERE p.login=:login OR t.login=:login";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($sql);
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
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($sql);
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
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($query);
        $pdoStatement->execute(["login" => $login]);
        $obj = $pdoStatement->fetch();
        return $obj[0];
    }

    public function participant()
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
        if (in_array($login, $this->participant(), true)) {
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

    public function getUtilisateur(): Utilisateur
    {
        $query = "SELECT {$this->formatNomsColonnes()} FROM {$this->getNomTable()} WHERE
        idtableau = {$this->getNomCle()}";
        $pdoStatement = ConnexionBaseDeDonnees::getPdo()->prepare($query);
        $pdoStatement->execute();
        $obj = $pdoStatement->fetch();
        return Utilisateur::construireDepuisTableau($obj);
    }
}