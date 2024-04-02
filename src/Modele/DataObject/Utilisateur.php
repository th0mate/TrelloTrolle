<?php

namespace App\Trellotrolle\Modele\DataObject;

class Utilisateur extends AbstractDataObject implements \JsonSerializable
{
    /**
     * Utilisateur constructor.
     * @param string|null $login L'identifiant de l'utilisateur
     * @param string|null $nom Le nom de l'utilisateur
     * @param string|null $prenom Le prénom de l'utilisateur
     * @param string|null $email L'email de l'utilisateur
     * @param string|null $mdpHache Le mot de passe haché de l'utilisateur
     */
    public function __construct(
        private ?string $login ,
        private ?string $nom,
        private ?string $prenom,
        private ?string $email,
        private ?string $mdpHache,
    )
    {}

    /**
     * Fonction permettant de construire un objet depuis un tableau de paramètres
     * @param array $objetFormatTableau Le tableau de paramètres
     * @return Utilisateur L'objet construit
     */
    public static function construireDepuisTableau(array $objetFormatTableau) : Utilisateur {

        return new Utilisateur(
            $objetFormatTableau["login"] ?? null,
            $objetFormatTableau["nom"] ?? null,
            $objetFormatTableau["prenom"] ?? null,
            $objetFormatTableau["email"] ?? null,
            $objetFormatTableau["mdphache"] ?? null,
        );
    }

    /**
     * Fonction permettant de récupérer l'identifiant de l'utilisateur
     * @return string|null L'identifiant de l'utilisateur
     */
    public function getLogin(): ?string
    {
        return $this->login;
    }

    /**
     * Fonction permettant de définir l'identifiant de l'utilisateur
     * @param string|null $login L'identifiant de l'utilisateur
     * @return void
     */
    public function setLogin(?string $login): void
    {
        $this->login = $login;
    }

    /**
     * Fonction permettant de récupérer le nom de l'utilisateur
     * @return string|null Le nom de l'utilisateur
     */
    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * Fonction permettant de définir le nom de l'utilisateur
     * @param string|null $nom Le nom de l'utilisateur
     * @return void
     */
    public function setNom(?string $nom): void
    {
        $this->nom = $nom;
    }

    /**
     * Fonction permettant de récupérer le prénom de l'utilisateur
     * @return string|null Le prénom de l'utilisateur
     */
    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    /**
     * Fonction permettant de définir le prénom de l'utilisateur
     * @param string|null $prenom Le prénom de l'utilisateur
     * @return void
     */
    public function setPrenom(?string $prenom): void
    {
        $this->prenom = $prenom;
    }

    /**
     * Fonction permettant de récupérer le mot de passe haché de l'utilisateur
     * @return string|null Le mot de passe haché de l'utilisateur
     */
    public function getMdpHache(): ?string
    {
        return $this->mdpHache;
    }

    /**
     * Fonction permettant de définir le mot de passe haché de l'utilisateur
     * @param string|null $mdpHache Le mot de passe haché de l'utilisateur
     * @return void
     */
    public function setMdpHache(?string $mdpHache): void
    {
        $this->mdpHache = $mdpHache;
    }

    /**
     * Fonction permettant de récupérer l'email de l'utilisateur
     * @return string|null L'email de l'utilisateur
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Fonction permettant de définir l'email de l'utilisateur
     * @param string|null $email L'email de l'utilisateur
     * @return void
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * Fonction permettant de formater l'objet en tableau
     * @return array L'objet formaté en tableau
     */
    public function formatTableau(): array
    {
        return array(
            "loginTag" => $this->login,
            "nomTag" => $this->nom,
            "prenomTag" => $this->prenom,
            "emailTag" => $this->email,
            "mdphacheTag" => $this->mdpHache,
        );
    }

    /**
     * Fonction permettant de sérialiser l'objet en JSON
     * @return mixed L'objet sérialisé
     */
    public function jsonSerialize() :mixed
    {
        return [
            "login"=>$this->login,
            "nom"=>$this->nom,
            "prenom"=>$this->prenom,
        ];
    }
}