<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use PDOException;

abstract class AbstractRepository implements AbstractRepositoryInterface
{
    
    public function __construct(protected ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees)
    {
    }

    protected abstract function getNomTable(): string;
    protected abstract function getNomCle(): string;
    protected abstract function getNomsColonnes(): array;
    protected abstract function construireDepuisTableau(array $objetFormatTableau) : AbstractDataObject;
    protected abstract function getAllFromTable(int|string $idCle) : ?AbstractDataObject;


    protected function formatNomsColonnes() : string {
        return join(",",$this->getNomsColonnes());
    }

    /**
     * @return AbstractDataObject[]
     */
    public function recuperer(): array
    {
        $nomTable = $this->getNomTable();
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->query("SELECT DISTINCT {$this->getNomCle()} 
        FROM $nomTable");
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->getAllFromTable($objetFormatTableau[$this->getNomCle()]);
        }

        return $objets;
    }

    /**
     * @return AbstractDataObject[]
     */
    protected function recupererOrdonne($attributs, $sens = "ASC"): array
    {
        $nomTable = $this->getNomTable();
        $attributsTexte = join(",", $attributs);
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->query("SELECT DISTINCT {$this->formatNomsColonnes()} FROM $nomTable ORDER BY $attributsTexte $sens");
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }

        return $objets;
    }

    /**
     * @param string $nomAttribut
     * @param $valeur
     * @return array|null
     */
    protected function recupererPlusieursPar(string $nomAttribut, $valeur): ?array
    {
        $nomTable = $this->getNomTable();
        $sql = "SELECT DISTINCT {$this->getNomCle()} from $nomTable WHERE $nomAttribut='$valeur'";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute();
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->getAllFromTable($objetFormatTableau[$this->getNomCle()]);
        }
        return $objets;

    }

    /**
     * @return AbstractDataObject[]
     */
    protected function recupererPlusieursParOrdonne(string $nomAttribut, $valeur, $attributs, $sens = "ASC"): ?array
    {
        $nomTable = $this->getNomTable();
        $attributsTexte = join(",", $attributs);
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare("SELECT DISTINCT {$this->getNomCle()} FROM $nomTable WHERE $nomAttribut=:valeur ORDER BY $attributsTexte $sens");
        $values = array(
            "valeur" => $valeur,
        );
        $pdoStatement->execute($values);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->getAllFromTable($objetFormatTableau[$this->getNomCle()]);
        }
        return $objets;
    }

    //TODO: Disuter de l'utilité de cette fonction car c'est la même chose que recupererPlusieursPar sauf qu'a
    // la base c'était une fonction qui devait retourner qu'un seul objet car on partait du principe qu'elle était appelé que
    // par recupererParClePrimaire
    /*protected function recupererPar(string $nomAttribut, $valeur): ?array
    {
        $nomTable = $this->getNomTable();
        $sql = "SELECT DISTINCT {$this->getNomCle()} from $nomTable WHERE $nomAttribut='$valeur'";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute();
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->getAllFromTable($objetFormatTableau[$this->getNomCle()]);
        }
        if ($objets) {
            return $objets;
        }
        return null;
    }*/



    public function recupererParClePrimaire(string $valeurClePrimaire): ?AbstractDataObject
    {
        return $this->recupererPlusieursPar($this->getNomCle(), $valeurClePrimaire)[0];
    }

    public function supprimer(string $valeurClePrimaire): bool
    {
        $nomTable = $this->getNomTable();
        $nomClePrimaire = $this->getNomCle();
        $sql = "DELETE FROM $nomTable WHERE $nomClePrimaire='$valeurClePrimaire';";
        $pdoStatement = $this->connexionBaseDeDonnees->getPDO()->query($sql);
        $deleteCount = $pdoStatement->rowCount();

        return ($deleteCount > 0);
    }

    public function mettreAJour(AbstractDataObject $object): void
    {
        $nomTable = $this->getNomTable();
        $nomClePrimaire = $this->getNomCle();
        $nomsColonnes = $this->getNomsColonnes();

        $partiesSet = array_map(function ($nomcolonne) {
            return "$nomcolonne = :{$nomcolonne}Tag";
        }, $nomsColonnes);
        $setString = join(',', $partiesSet);
        $whereString = "$nomClePrimaire = :{$nomClePrimaire}Tag";

        $sql = "UPDATE $nomTable SET $setString WHERE $whereString";
        $req_prep = $this->connexionBaseDeDonnees->getPDO()->prepare($sql);

        $objetFormatTableau = $object->formatTableau();
        $req_prep->execute($objetFormatTableau);

    }

    public function ajouter(AbstractDataObject $object): bool
    {
        $nomTable = $this->getNomTable();
        $nomsColonnes = $this->getNomsColonnes();

        $insertString = '(' . join(', ', $nomsColonnes) . ')';

        $partiesValues = array_map(function ($nomcolonne) {
            return ":{$nomcolonne}Tag";
        }, $nomsColonnes);
        $valueString = '(' . join(', ', $partiesValues) . ')';

        $sql = "INSERT INTO $nomTable $insertString VALUES $valueString";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);;

        $objetFormatTableau = $object->formatTableau();

        try {
            $pdoStatement->execute($objetFormatTableau);
            return true;
        } catch (PDOException $exception) {
            if ($pdoStatement->errorCode() === "23000") {
                return false;
            } else {
                throw $exception;
            }
        }
    }

    protected function getNextId(string $type) : int {
        $nomTable = $this->getNomTable();
        $query = $this->connexionBaseDeDonnees->getPdo()->query("SELECT MAX($type) FROM $nomTable");
        $query->execute();
        $obj = $query->fetch();
        return $obj[0] === null ? 0 : $obj[0] + 1;
    }

}
