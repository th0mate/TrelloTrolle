<?php

namespace App\Trellotrolle\Modele\DataObject;

class Utilisateur extends AbstractDataObject implements \JsonSerializable
{
    /**
     * @param string|null $login
     * @param string|null $nom
     * @param string|null $prenom
     * @param string|null $email
     * @param string|null $mdpHache
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
     * @param array $objetFormatTableau
     * @return Utilisateur
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
     * @return string|null
     */
    public function getLogin(): ?string
    {
        return $this->login;
    }

    /**
     * @param string|null $login
     * @return void
     */
    public function setLogin(?string $login): void
    {
        $this->login = $login;
    }

    /**
     * @return string|null
     */
    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * @param string|null $nom
     * @return void
     */
    public function setNom(?string $nom): void
    {
        $this->nom = $nom;
    }

    /**
     * @return string|null
     */
    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    /**
     * @param string|null $prenom
     * @return void
     */
    public function setPrenom(?string $prenom): void
    {
        $this->prenom = $prenom;
    }

    /**
     * @return string|null
     */
    public function getMdpHache(): ?string
    {
        return $this->mdpHache;
    }

    /**
     * @param string|null $mdpHache
     * @return void
     */
    public function setMdpHache(?string $mdpHache): void
    {
        $this->mdpHache = $mdpHache;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     * @return void
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return array
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
     * @return mixed
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