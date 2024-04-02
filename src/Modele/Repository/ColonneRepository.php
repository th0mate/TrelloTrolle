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
            "idcolonne", "titrecolonne", "idtableau", "ordre"
        ];
    }

    protected function construireDepuisTableau(array $objetFormatTableau): Colonne
    {
        return Colonne::construireDepuisTableau($objetFormatTableau);
    }

    public function recupererColonnesTableau(int $idTableau): ?array
    {
        $query = "SELECT idtableau FROM tableau WHERE idtableau=:idTableau";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["idTableau" => $idTableau]);
        $objet = $pdoStatement->fetch();
        if (!$objet) {
            return null;
        }
        return $this->recupererPlusieursParOrdonne("idtableau", $idTableau, ["ordre"]);
    }

    public function getNextIdColonne(): int
    {
        return $this->getNextId("idcolonne");
    }

    public function getNombreColonnesTotalTableau(int $idTableau): int
    {
        $query = "SELECT COUNT(DISTINCT idcolonne) FROM {$this->getNomTable()} WHERE idtableau=:idTableau";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["idTableau" => $idTableau]);
        $obj = $pdoStatement->fetch();
        return $obj[0];
    }


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


    /**
     * Récupère le prochain ordre de colonne pour un tableau donné
     * @param int $idTableau l'id du tableau en question
     * @return int|mixed l'ordre suivant du tableau
     */
    public function getNextOrdreColonne(int $idTableau)
    {
        $query = "SELECT MAX(ordre) FROM {$this->getNomTable()} WHERE idtableau=:idTableau";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["idTableau" => $idTableau]);
        $obj = $pdoStatement->fetch();
        return $obj[0] + 1;
    }


    /**
     * Inverse l'ordre de deux colonnes
     */
    public function inverserOrdreColonnes(int $idColonne1, int $idColonne2): void
    {
        $colonne1 = $this->recupererParClePrimaire($idColonne1);
        $colonne2 = $this->recupererParClePrimaire($idColonne2);
        $ordre1 = $colonne1->getOrdre();
        $ordre2 = $colonne2->getOrdre();

        $sql1 = "UPDATE {$this->getNomTable()} SET ordre=:ordre1 WHERE idcolonne=:idColonne1";
        $sql2 = "UPDATE {$this->getNomTable()} SET ordre=:ordre2 WHERE idcolonne=:idColonne2";

        $pdo = $this->connexionBaseDeDonnees->getPdo();
        $pdoStatement1 = $pdo->prepare($sql1);
        $pdoStatement1->execute(["ordre1" => $ordre2, "idColonne1" => $idColonne1]);

        $pdoStatement2 = $pdo->prepare($sql2);
        $pdoStatement2->execute(["ordre2" => $ordre1, "idColonne2" => $idColonne2]);
    }


}