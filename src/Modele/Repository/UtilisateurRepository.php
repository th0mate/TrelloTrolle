<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use Exception;

class UtilisateurRepository extends AbstractRepository implements UtilisateurRepositoryInterface
{

    /**
     * Fonction permettant de récupérer le nom de la table
     * @return string
     */
    protected function getNomTable(): string
    {
        return "Utilisateur";
    }

    /**
     * Fonction permettant de récupérer le nom de la clé primaire
     * @return string
     */
    protected function getNomCle(): string
    {
        return "login";
    }

    /**
     * Fonction permettant de récupérer les noms des colonnes
     * @return string[] Les noms des colonnes
     */
    protected function getNomsColonnes(): array
    {
        return ["login", "nom", "prenom", "email", "mdphache", "nonce"];
    }

    /**
     * Fonction permettant de construire un objet depuis un tableau de paramètres
     * @param array $objetFormatTableau Le tableau de paramètres
     * @return AbstractDataObject L'objet construit
     */
    protected function construireDepuisTableau(array $objetFormatTableau): Utilisateur
     {
        return Utilisateur::construireDepuisTableau($objetFormatTableau);
    }


    /**
     * Fonction permettant de récupérer un utilisateur en fonction de son email
     * @param string $email L'email de l'utilisateur
     * @return array Les utilisateurs récupérés
     */
    public function recupererUtilisateursParEmail(string $email): ?Utilisateur
    {
        return $this->recupererPlusieursPar("email", $email)[0];
    }

    /**
     * Fonction permettant de récupérer un utilisateur en fonction prenom et nom
     * @return array Les utilisateurs récupérés
     */
    public function recupererUtilisateursOrderedPrenomNom(): array
    {
        return $this->recupererOrdonne(["prenom", "nom"]);
    }

    /**
     * Fonction permettant de récupérer un utilisateur en fonction d'une recherche
     * @param $recherche , La recherche
     * @return array Les utilisateurs récupérés
     */
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


    /**
     * Fonction permettant de récupérer un utilisateur en fonction de sa clé primaire
     * @param int|string $idCle La clé primaire
     * @return Utilisateur|null L'utilisateur récupéré
     */
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