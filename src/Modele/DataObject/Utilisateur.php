<?php

namespace App\Trellotrolle\Modele\DataObject;

class Utilisateur extends AbstractDataObject implements \JsonSerializable
{
    public function __construct(
        private ?string $login ,
        private string $nom,
        private string $prenom,
        private string $email,
        private string $mdpHache,
    )
    {var_dump($this->nom);}

    public static function construireDepuisTableau(array $objetFormatTableau) : Utilisateur {

        return new Utilisateur(
            $objetFormatTableau["login"] ,
            $objetFormatTableau["nom"] ,
            $objetFormatTableau["prenom"],
            $objetFormatTableau["email"],
            $objetFormatTableau["mdphache"],
        );
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(?string $login): void
    {
        $this->login = $login;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): void
    {
        $this->nom = $nom;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): void
    {
        $this->prenom = $prenom;
    }

    public function getMdpHache(): ?string
    {
        return $this->mdpHache;
    }

    public function setMdpHache(?string $mdpHache): void
    {
        $this->mdpHache = $mdpHache;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

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

    public function jsonSerialize() :mixed
    {
        return [
            "login"=>$this->login,
            "nom"=>$this->nom,
            "prenom"=>$this->prenom,
        ];
    }
}