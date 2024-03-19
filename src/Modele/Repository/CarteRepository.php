<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Carte;
use Exception;

class CarteRepository extends AbstractRepository
{

    protected function getNomTable(): string
    {
        return "app_db";
    }

    protected function getNomCle(): string
    {
        return "idcarte";
    }

    protected function getNomsColonnes(): array
    {
        return [
            "login", "nom", "prenom", "email", "mdphache",
            "mdp", "idtableau", "codetableau", "titretableau",
            "participants", "idcolonne", "titrecolonne",
            "idcarte", "titrecarte", "descriptifcarte", "couleurcarte", "affectationscarte"
        ];
    }

    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        return Carte::construireDepuisTableau($objetFormatTableau);
    }

    public function recupererCartesColonne(int $idcolonne): array {
        return $this->recupererPlusieursPar("idcolonne", $idcolonne);
    }

    public function recupererCartesTableau(int $idTableau): array {
        return $this->recupererPlusieursPar("idtableau", $idTableau);
    }

    /**
     * @return Carte[]
     */
    public function recupererCartesUtilisateur(string $login): array
    {
        $sql = "SELECT {$this->formatNomsColonnes()} from app_db WHERE affectationscarte @> :json";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $values = array(
            "json" => json_encode(["utilisateurs" => [["login" => $login]]])
        );
        $pdoStatement->execute($values);
        $objets = [];
        foreach ($pdoStatement as $objetFormatTableau) {
            $objets[] = $this->construireDepuisTableau($objetFormatTableau);
        }
        return $objets;
    }

    public function getNombreCartesTotalUtilisateur(string $login) : int {
        $query = "SELECT COUNT(*) FROM app_db WHERE login=:login";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["login" => $login]);
        $obj = $pdoStatement->fetch();
        return $obj[0];
    }

    public function getNextIdCarte() : int {
        return $this->getNextId("idcarte");
    }
}