<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use Exception;

class ColonneRepository extends AbstractRepository implements ColonneRepositoryInterface
{


    protected function getNomTable(): string
    {
        return "colonne";
    }

    protected function getNomCle(): string
    {
        return "idcolonne";
    }

    protected function getNomsColonnes(): array
    {
        return [
            "idcolonne", "titrecolonne", "idtableau"
        ];
    }

    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        return Colonne::construireDepuisTableau($objetFormatTableau);
    }

    public function recupererColonnesTableau(int $idTableau): array {
        return $this->recupererPlusieursParOrdonne("idtableau", $idTableau, ["idcolonne"]);
    }

    public function getNextIdColonne() : int {
        return $this->getNextId("idcolonne");
    }

    public function getNombreColonnesTotalTableau(int $idTableau) : int {
        $query = "SELECT COUNT(DISTINCT idcolonne) FROM {$this->getNomTable()} WHERE idtableau=:idTableau";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["idTableau" => $idTableau]);
        $obj = $pdoStatement->fetch();
        return $obj[0];
    }

    public function inverserOrdreColonnes(int $idColonne1, int $idColonne2): void
    {
        $query = "UPDATE {$this->getNomTable()} SET idcolonne = CASE idcolonne WHEN :idColonne1 THEN :idColonne2 WHEN :idColonne2 THEN :idColonne1 END WHERE idcolonne IN (:idColonne1, :idColonne2)";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["idColonne1" => $idColonne1, "idColonne2" => $idColonne2]);
    }

    public function getAllFromColonne(int $idColonne): Colonne
    {
        $query = "SELECT * FROM {$this->getNomTable()} co
        JOIN tableau ta ON co.idtableau=ta.idtableau
        JOIN utilisateur u ON ta.login=u.login
        WHERE idcolonne=:idColonne";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["idColonne" => $idColonne]);

        return $this->construireDepuisTableau($pdoStatement->fetch());
    }


}