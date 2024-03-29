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
        return ["login", "nom", "prenom", "email", "mdphache"];
    }

    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        return Utilisateur::construireDepuisTableau($objetFormatTableau);
    }


    public function recupererUtilisateursParEmail(string $email): array
    {
        return $this->recupererPlusieursPar("email", $email);
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
}