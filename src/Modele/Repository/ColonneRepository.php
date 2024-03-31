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
     * @return string
     */
    protected function getNomTable(): string
    {
        return "colonne";
    }

    /**
     * @return string
     */
    protected function getNomCle(): string
    {
        return "idcolonne";
    }

    /**
     * @return string[]
     */
    protected function getNomsColonnes(): array
    {
        return [
            "idcolonne", "titrecolonne", "idtableau"
        ];
    }

    /**
     * @param array $objetFormatTableau
     * @return AbstractDataObject
     */
    protected function construireDepuisTableau(array $objetFormatTableau): Colonne
    {
        return Colonne::construireDepuisTableau($objetFormatTableau);
    }

    /**
     * @param int $idTableau
     * @return array
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
     * @return int
     */
    public function getNextIdColonne(): int
    {
       return $this->getNextId("idcolonne");
    }

    /**
     * @param int $idTableau
     * @return int
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
     * @param int $idColonne1
     * @param int $idColonne2
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
     * @param int $idColonne
     * @return array
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