<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use PDOException;

abstract class AbstractRepository implements AbstractRepositoryInterface
{

    /**
     * AbstractRepository constructor.
     * @param ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees Interface de connexion à la base de données
     */


    public function __construct(protected ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees){}

    /**
     * Fonction permettant de récupérer le nom de la table
     * @return string Le nom de la table
     */
    protected abstract function getNomTable(): string;

    /**
     * Fonction permettant de récupérer le nom de la clé primaire
     * @return string Le nom de la clé primaire
     */
    protected abstract function getNomCle(): string;

    /**
     * Fonction permettant de récupérer les noms des colonnes
     * @return array Les noms des colonnes
     */
    protected abstract function getNomsColonnes(): array;

    /**
     * Fonction permettant de construire un objet depuis un tableau de paramètres
     * @param array $objetFormatTableau Le tableau de paramètres
     * @return AbstractDataObject L'objet construit
     */
    protected abstract function construireDepuisTableau(array $objetFormatTableau) : AbstractDataObject;

    /**
     * Fonction permettant de récupérer un objet de la table en fonction de la clé primaire
     * @param int|string $idCle La clé primaire
     * @return AbstractDataObject|null L'objet récupéré
     */
    protected abstract function getAllFromTable(int|string $idCle) : ?AbstractDataObject;


    /**
     * Fonction permettant d'obtenir les noms des colonnes sous forme de string
     * @return string Les noms des colonnes listés
     */
    protected function formatNomsColonnes() : string {
        return join(",",$this->getNomsColonnes());
    }

    /**
     * Fonction permettant de récupérer tous les objets de la table
     * @return AbstractDataObject[] Les objets récupérés
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
     * Fonction permettant de récupérer tous les objets de la table de manière ordonnée
     * @return AbstractDataObject[] Les objets récupérés rangés par ordre croissant (ASC)
     */
    protected function recupererOrdonne($attributs, $sens = "ASC"): array
    {
        $nomTable = $this->getNomTable();
        $attributsTexte = join(",", $attributs);
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare("SELECT DISTINCT {$this->formatNomsColonnes()} FROM $nomTable ORDER BY $attributsTexte $sens");
        $pdoStatement->execute();
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }

        return $objets;
    }

    /**
     * Fonction permettant de récupérer tous les objets de la table
     * avec un attribut spécifique et une valeur précise
     * @param string $nomAttribut Le nom de l'attribut
     * @param $valeur , La valeur de l'attribut
     * @return array|null Les objets récupérés
     */
    protected function recupererPlusieursPar(string $nomAttribut, $valeur): ?array
    {
        $nomTable = $this->getNomTable();
        $sql = "SELECT DISTINCT {$this->getNomCle()} from $nomTable WHERE $nomAttribut='$valeur'";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->query($sql);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->getAllFromTable($objetFormatTableau[$this->getNomCle()]);
        }
        return $objets;

    }

    /**
     * Fonction permettant de récupérer tous les objets de la table
     * avec un attribut spécifique et une valeur précise
     * dans un l'ordre spécifié (par défaut croissant)
     * @return AbstractDataObject[] Les objets récupérés
     */
    protected function recupererPlusieursParOrdonne(string $nomAttribut, $valeur, $attributs, $sens = "ASC"): ?array
    {
        $nomTable = $this->getNomTable();
        $attributsTexte = join(",", $attributs);
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare("SELECT DISTINCT {$this->getNomCle()},ordre FROM $nomTable WHERE $nomAttribut=:valeur ORDER BY $attributsTexte $sens");
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


    /**
     * Fonction permettant de récupérer un objet de la table en fonction de la clé primaire
     * @param string $valeurClePrimaire La clé primaire
     * @return AbstractDataObject|null L'objet récupéré
     */
    public function recupererParClePrimaire(string $valeurClePrimaire): ?AbstractDataObject
    {
        return $this->recupererPlusieursPar($this->getNomCle(), $valeurClePrimaire)[0];
    }

    /**
     * Fonction permettant de supprimer un objet de la table en fonction de la clé primaire
     * @param string $valeurClePrimaire La clé primaire
     * @return bool Vrai si l'objet a été supprimé, faux sinon
     */
    public function supprimer(string $valeurClePrimaire): bool
    {
        $nomTable = $this->getNomTable();
        $nomClePrimaire = $this->getNomCle();
        $sql = "DELETE FROM $nomTable WHERE $nomClePrimaire='$valeurClePrimaire';";
        $pdoStatement = $this->connexionBaseDeDonnees->getPDO()->prepare($sql);
        $pdoStatement->execute();
        $deleteCount = $pdoStatement->rowCount();

        return ($deleteCount > 0);
    }

    /**
     * Fonction permettant de mettre à jour un objet de la table
     * @param AbstractDataObject $object L'objet à mettre à jour
     * @return void
     */
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

    /**
     * Fonction permettant d'ajouter un objet à la table
     * @param AbstractDataObject $object L'objet à ajouter
     * @return bool Vrai si l'objet a été ajouté, faux sinon
     */
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

    /**
     * Fonction permettant de récupérer le prochain id de la table
     * @param string $type Le type de la colonne
     * @return int L'id suivant
     */
    protected function getNextId(string $type) : int {
        $nomTable = $this->getNomTable();
        $query = $this->connexionBaseDeDonnees->getPdo()->query("SELECT MAX($type) FROM $nomTable");
        $obj = $query->fetch();
        return $obj[0] === null ? 0 : $obj[0] + 1;
    }

}
