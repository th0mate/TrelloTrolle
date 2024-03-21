<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use PDOException;

abstract class AbstractRepository
{
    
    public function __construct(protected ConnexionBaseDeDonnees $connexionBaseDeDonnees)
    {
    }

    protected abstract function getNomTable(): string;
    protected abstract function getNomCle(): string;
    protected abstract function getNomsColonnes(): array;
    protected abstract function construireDepuisTableau(array $objetFormatTableau) : AbstractDataObject;

    private function myDbInt() {
        //TO-DO ! Important !
    }

    protected function formatNomsColonnes() : string {
        return join(",",$this->getNomsColonnes());
    }

    /**
     * @return AbstractDataObject[]
     */
    public function recuperer(): array
    {
        $nomTable = $this->getNomTable();
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->query("SELECT DISTINCT {$this->formatNomsColonnes()} FROM $nomTable");

        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
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
     * @return AbstractDataObject[]
     */
    protected function recupererPlusieursPar(string $nomAttribut, $valeur): array
    {
        $nomTable = $this->getNomTable();
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare("SELECT DISTINCT {$this->formatNomsColonnes()} FROM $nomTable WHERE $nomAttribut='$valeur'");
        $pdoStatement->execute();
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }

        return $objets;
    }

    /**
     * @return AbstractDataObject[]
     */
    protected function recupererPlusieursParOrdonne(string $nomAttribut, $valeur, $attributs, $sens = "ASC"): array
    {
        $nomTable = $this->getNomTable();
        $attributsTexte = join(",", $attributs);
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare("SELECT DISTINCT {$this->formatNomsColonnes()} FROM $nomTable WHERE $nomAttribut=:valeur ORDER BY $attributsTexte $sens");
        $values = array(
            "valeur" => $valeur,
        );
        $pdoStatement->execute($values);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }

        return $objets;
    }

    protected function recupererPar(string $nomAttribut, $valeur): ?AbstractDataObject
    {
        $nomTable = $this->getNomTable();
        $sql = "SELECT DISTINCT {$this->formatNomsColonnes()} from $nomTable WHERE $nomAttribut='$valeur'";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute();
        $objetFormatTableau = $pdoStatement->fetch();

        if ($objetFormatTableau !== false) {
            return $this->construireDepuisTableau($objetFormatTableau);
        }
        return null;
    }

    public function recupererParClePrimaire(string $valeurClePrimaire): ?AbstractDataObject
    {
        return $this->recupererPar($this->getNomCle(), $valeurClePrimaire);
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
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);

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
