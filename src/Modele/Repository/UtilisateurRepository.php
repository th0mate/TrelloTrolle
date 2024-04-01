<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use Exception;

class UtilisateurRepository extends AbstractRepository implements UtilisateurRepositoryInterface
{

    protected function getNomTable(): string
    {
        return "Utilisateur";
    }

    protected function getNomCle(): string
    {
        return "login";
    }

    protected function getNomsColonnes(): array
    {
        return ["login", "nom", "prenom", "email", "mdphache", "nonce"];
    }

    protected function construireDepuisTableau(array $objetFormatTableau): Utilisateur
    {
        return Utilisateur::construireDepuisTableau($objetFormatTableau);
    }


    public function recupererUtilisateursParEmail(string $email): ?Utilisateur
    {
        return $this->recupererPlusieursPar("email", $email)[0];
    }

    public function recupererUtilisateursOrderedPrenomNom(): array
    {
        return $this->recupererOrdonne(["prenom", "nom"]);
    }

    public function recherche($recherche): array
    {
        $recherche = strtolower($recherche) . "%";
        $sql = "SELECT {$this->formatNomsColonnes()} FROM {$this->getNomTable()} WHERE LOWER(login) LIKE :tagLogin OR LOWER(nom) LIKE :tagNom OR LOWER(prenom) LIKE :tagPrenom OR LOWER(email) LIKE :tagMail";
        $values = ["tagLogin" => $recherche, "tagNom" => $recherche, "tagPrenom" => $recherche, "tagMail" => $recherche];
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute($values);
        $utlisateurs = [];
        foreach ($pdoStatement as $utilisateur) {
            $utlisateurs[] = $this->construireDepuisTableau($utilisateur);
        }
        return $utlisateurs;
    }


    public function getAllFromTable(int|string $idCle): ?Utilisateur
    {
        $query = "SELECT * FROM {$this->getNomTable()}
        WHERE login=:login";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($query);
        $pdoStatement->execute(["login" => $idCle]);
        $objetFormatTableau = $pdoStatement->fetch();
        if (!$objetFormatTableau) {
            return null;
        }
        return $this->construireDepuisTableau($objetFormatTableau);

    }
}