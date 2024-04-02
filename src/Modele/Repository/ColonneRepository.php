<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use Exception;

class ColonneRepository extends AbstractRepository implements ColonneRepositoryInterface
{


    /**
     * Fonction permettant de récupérer le nom de la table
     * @return string Le nom de la table
     */
    protected function getNomTable(): string
    {
        return "colonne";
    }

    /**
     * Fonction permettant de récupérer l'id de la colonne
     * @return string l'id de la colonne
     */
    protected function getNomCle(): string
    {
        return "idcolonne";
    }

    /**
     * Fonction permettant de récupérer les noms des colonnes
     * @return string[] Les noms des colonnes
     */
    protected function getNomsColonnes(): array
    {
        return [
            "idcolonne", "titrecolonne", "idtableau", "ordre"
        ];
    }

    /**
     * Fonction permettant de construire une colonne depuis un tableau de paramètres
     * @param array $objetFormatTableau Le tableau de paramètres
     * @return AbstractDataObject La colonne construite
     */
    protected function construireDepuisTableau(array $objetFormatTableau): Colonne
    {
        return Colonne::construireDepuisTableau($objetFormatTableau);
    }

    /**
     * Fonction permettant de récupérer toutes les colonnes d'un tableau
     * @param int $idTableau L'id du tableau
     * @return array La liste des colonnes du tableau
     */
    public function recupererColonnesTableau(int $idTableau): ?array {
        $query = "SELECT idtableau FROM tableau WHERE idtableau=:idTableau";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["idTableau" => $idTableau]);
        $objet = $pdoStatement->fetch();
        if (!$objet) {
            return null;
        }
        return $this->recupererPlusieursParOrdonne("idtableau", $idTableau, ["idcolonne"]);
    }

    /**
     * Fonction permettant d'avoir le prochain id de colonne
     * @return int L'id de la prochaine colonne
     */
    public function getNextIdColonne(): int
    {
       return $this->getNextId("idcolonne");
    }

    /**
     * Fonction permettant de récupérer le nombre de colonnes total d'un tableau
     * @param int $idTableau L'id du tableau
     * @return int Le nombre de colonnes total du tableau
     */
    public function getNombreColonnesTotalTableau(int $idTableau): int
    {
       $query = "SELECT COUNT(DISTINCT idcolonne) FROM {$this->getNomTable()} WHERE idtableau=:idTableau";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["idTableau" => $idTableau]);
        $obj = $pdoStatement->fetch();
        return $obj[0];
    }

    /**
     * Fonction permettant d'inverser l'ordre de deux colonnes
     * @param int $idColonne1 Id de la première colonne
     * @param int $idColonne2 Id de la deuxième colonne
     * @return void
     */
    public function inverserOrdreColonnes(int $idColonne1, int $idColonne2): void
    {
        $colonne1=$this->recupererParClePrimaire($idColonne1);
        $colonne2=$this->recupererParClePrimaire($idColonne2);
        $tabColonne1 = array(
            "idcolonne"=>$colonne1->getIdColonne());
        $tabColonne2 = array(
            "idcolonne"=>$colonne2->getIdColonne());

        $query = "UPDATE {$this->getNomTable()} SET idcolonne = :tempId WHERE idcolonne = :idColonne1";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["tempId" => $this->getNextIdColonne(), "idColonne1" => $tabColonne1["idcolonne"]]);

        $query = "UPDATE {$this->getNomTable()} SET idcolonne = :idColonne1 
        WHERE idcolonne = :idColonne2";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["idColonne1" => $tabColonne1["idcolonne"], "idColonne2" => $tabColonne2["idcolonne"]]);

        $query = "UPDATE {$this->getNomTable()} SET idcolonne = :idColonne2
        WHERE idcolonne = :idColonne1";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["idColonne2" => $tabColonne2["idcolonne"], "idColonne1" => $tabColonne1["idcolonne"]]);
    }

    /**
     * Fonction permettant de récupérer une colonne en fonction de la clé primaire
     * @param int $idColonne La clé primaire
     * @return Colonne|null La colonne récupérée
     */

    public function getAllFromTable(int|string $idCle): ?Colonne
    {
        $query = "SELECT * FROM {$this->getNomTable()} co
        JOIN tableau ta ON co.idtableau=ta.idtableau
        JOIN utilisateur u ON ta.login=u.login
        WHERE idcolonne=:idColonne";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["idColonne" => $idCle]);
        $objetFormatTableau = $pdoStatement->fetch();
        if (!$objetFormatTableau) {
            return null;
        }
        return $this->construireDepuisTableau($objetFormatTableau);
    }


}