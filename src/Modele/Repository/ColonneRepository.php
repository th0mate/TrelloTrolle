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
    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        return Colonne::construireDepuisTableau($objetFormatTableau);
    }

    /**
     * @param int $idTableau
     * @return array
     */
    public function recupererColonnesTableau(int $idTableau): array {
        return $this->recupererPlusieursParOrdonne("idtableau", $idTableau, ["idcolonne"]);
    }

    /**
     * @return int
     */
    public function getNextIdColonne() : int {
        return $this->getNextId("idcolonne");
    }

    /**
     * @param int $idTableau
     * @return int
     */
    public function getNombreColonnesTotalTableau(int $idTableau) : int {
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
        $query = "UPDATE {$this->getNomTable()} SET idcolonne = CASE idcolonne WHEN :idColonne1 THEN :idColonne2 WHEN :idColonne2 THEN :idColonne1 END WHERE idcolonne IN (:idColonne1, :idColonne2)";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["idColonne1" => $idColonne1, "idColonne2" => $idColonne2]);
    }

    /**
     * @param int $idColonne
     * @return array
     */
    public function getAllFromColonne(int $idColonne): array
    {
        $query = "SELECT * FROM {$this->getNomTable()} co
        JOIN tableau ta ON co.idtableau=ta.idtableau
        JOIN utilisateur u ON ta.login=u.login
        WHERE idcarte=:idColonne";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["idColonne" => $idColonne]);
        $obj = [];
        foreach($pdoStatement as $objetFormatTableau) {
            $obj[] = $objetFormatTableau;
        }
        return $obj;
    }


}