<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use Exception;

class UtilisateurRepository extends AbstractRepository implements UtilisateurRepositoryInterface
{

    /**
     * @return string
     */
    protected function getNomTable(): string
    {
        return "Utilisateur";
    }

    /**
     * @return string
     */
    protected function getNomCle(): string
    {
        return "login";
    }

    /**
     * @return string[]
     */
    protected function getNomsColonnes(): array
    {
        return ["login", "nom", "prenom", "email", "mdphache"];
    }

    /**
     * @param array $objetFormatTableau
     * @return AbstractDataObject
     */
    protected function construireDepuisTableau(array $objetFormatTableau): AbstractDataObject
    {
        return Utilisateur::construireDepuisTableau($objetFormatTableau);
    }


    /**
     * @param string $email
     * @return array
     */
    public function recupererUtilisateursParEmail(string $email): array
    {
        return $this->recupererPlusieursPar("email", $email);
    }

    /**
     * @return array
     */
    public function recupererUtilisateursOrderedPrenomNom(): array
    {
        return $this->recupererOrdonne(["prenom", "nom"]);
    }

    /**
     * @param $recherche
     * @return array
     */
    public function recherche($recherche)
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