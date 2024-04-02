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
     * Fonction permettant de récupérer le nom de la table
     * @return string Le nom de la table
     */
    protected function getNomTable(): string
    {
        return "tableau";
    }

    /**
     * Fonction permettant de récupérer le nom de la clé primaire
     * @return string Le nom de la clé primaire
     */
    protected function getNomCle(): string
    {
        return "idtableau";
    }

    /**
     * Fonction permettant de récupérer les noms des colonnes de la table
     * @return string[] Les noms des colonnes
     */
    protected function getNomsColonnes(): array
    {
        return ["idtableau", "codetableau", "titretableau", "login"];
    }

    /**
     * Fonction permettant de construire un objet depuis un tableau de paramètres
     * @param array $objetFormatTableau Le tableau de paramètres
     * @return AbstractDataObject L'objet construit
     */
    protected function construireDepuisTableau(array $objetFormatTableau): Tableau
    {
        return Tableau::construireDepuisTableau($objetFormatTableau);
    }

    /**
     * Fonction permettant de récupérer tous les tableaux d'un utilisateur
     * @param string $login Le login de l'utilisateur
     * @return array Les tableaux de l'utilisateur
     */
    public function recupererTableauxUtilisateur(string $login): array
    {
        return $this->recupererPlusieursPar("login", $login);
    }

    /**
     * Fonction permettant de récupérer un tableau en fonction de son code
     * @param string $codeTableau Le code du tableau
     * @return AbstractDataObject|null Le tableau récupéré
     */
    public function recupererParCodeTableau(string $codeTableau): ?AbstractDataObject
    {
        $query = "SELECT idtableau,codetableau,titretableau,t.login, nom,prenom,email,mdphache, nonce FROM {$this->getNomTable()} t
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
     * Fonction permettant de récupérer un tableau en fonction d'un login utilisateur
     * @return Tableau[] Les tableaux récupérés dont l'utilisateur est membre
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
            $objets[] = $this->getAllFromTable($objetFormatTableau["idtableau"]);
        }
        return $objets;
    }

    /**
     * Fonction permettant de récupérer un tableau en fonction d'un login utilisateur
     * @return Tableau[] Les tableaux récupérés dont l'utilisateur est participant
     */
    public function recupererTableauxParticipeUtilisateur(string $login): array
    {
        $sql = "SELECT DISTINCT p.idTableau
                from {$this->getNomTable()} t JOIN participant p ON t.idtableau = p.idtableau
                WHERE p.login = :login";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["login" => $login]);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->getAllFromTable($objetFormatTableau["idtableau"]);
        }
        return $objets;
    }

    /**
     * Fonction permettant de récupérer le prochain id de tableau
     * @return int L'id du prochain tableau
     */
    public function getNextIdTableau(): int
    {
        return $this->getNextId("idtableau");
    }

    /**
     * Fonction permettant de récupérer le nombre de tableaux total d'un utilisateur
     * @param string $login Le login de l'utilisateur
     * @return int Le nombre de tableaux total où l'utilisateur est membre
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
     * Fonction permettant de vérifier si un utilisateur est membre d'un tableau
     * @param string $login Le login de l'utilisateur
     * @param Tableau $tableau Le tableau
     * @return bool Vrai si l'utilisateur est membre, faux sinon
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
     * Fonction permettant de vérifier si un utilisateur est propriétaire d'un tableau
     * @param $login string Le login de l'utilisateur
     * @param Tableau $tableau Le tableau
     * @return bool Vrai si l'utilisateur est propriétaire, faux sinon
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
     * Fonction permettant de vérifier si un utilisateur est participant ou propriétaire d'un tableau
     * @param string $login Le login de l'utilisateur
     * @param Tableau $tableau Le tableau
     * @return bool Vrai si l'utilisateur est participant ou propriétaire, faux sinon
     */
    public function estParticipantOuProprietaire(string $login, Tableau $tableau): bool
    {
        return $this->estProprietaire($login, $tableau) || $this->estParticipant($login, $tableau);
    }

    /**
     * Fonction permettant de récupérer les participants d'un tableau
     * @param Tableau $tableau Le tableau
     * @return array|null Les participants du tableau
     */
    public function getParticipants(Tableau $tableau): ?array
    {
        $query = "SELECT u.login,nom,prenom,email,mdphache, nonce
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
     * Fonction permettant de mettre à jour les participants d'un tableau
     * @param array|null $participants Les participants
     * @param Tableau $tableau Le tableau
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
     * Fonction permettant de récupérer le propriétaire d'un tableau
     * @param Tableau $tableau Le tableau
     * @return Utilisateur Le propriétaire du tableau
     */
    public function getProprietaire(Tableau $tableau) : Utilisateur
    {
        $query = "SELECT u.login,nom,prenom,email,mdphache, nonce
        FROM {$this->getNomTable()} t
        JOIN utilisateur u ON t.login=u.login
        WHERE t.{$this->getNomCle()}=:idtableau";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["idtableau" => $tableau->getIdTableau()]);
        $objetFormatTableau = $pdoStatement->fetch();
        return Utilisateur::construireDepuisTableau($objetFormatTableau);
    }

    /**
     * Fonction permettant de récupérer un tableau en fonction de la clé primaire
     * @param int $idTableau La clé primaire
     * @return Tableau|null Le tableau récupéré sous forme d'array
     */
    public function getAllFromTable(int|string $idCle): ?Tableau
   {
        $query = "SELECT * FROM {$this->getNomTable()} ta
        JOIN utilisateur u ON ta.login=u.login
        WHERE idtableau=:idTableau";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["idTableau" => $idCle]);
        $objetFormatTableau = $pdoStatement->fetch();
        if (!$objetFormatTableau) {
            return null;
        }
        return $this->construireDepuisTableau($objetFormatTableau);
    }

}