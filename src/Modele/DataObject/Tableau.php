<?php

namespace App\Trellotrolle\Modele\DataObject;

use App\Trellotrolle\Modele\Repository\TableauRepository;

class Tableau extends AbstractDataObject implements \JsonSerializable
{
    /**
     * Tableau constructor.
     * @param int|null $idTableau L'id du tableau
     * @param string|null $codeTableau Le code du tableau
     * @param string|null $titreTableau Le titre du tableau
     * @param Utilisateur|null $utilisateur L'utilisateur propriétaire du tableau
     */
    public function __construct(
        private ?int         $idTableau,
        private ?string      $codeTableau,
        private ?string      $titreTableau,
        private ?Utilisateur $utilisateur,
    )
    {
    }

    /**
     * Fonction permettant de construire un objet depuis un tableau de paramètres
     * @param array $objetFormatTableau Le tableau de paramètres
     * @return Tableau L'objet construit
     */
    public static function construireDepuisTableau(array $objetFormatTableau): Tableau
    {

        return new Tableau(
            $objetFormatTableau["idtableau"] ?? null,
            $objetFormatTableau["codetableau"] ?? null,
            $objetFormatTableau["titretableau"] ?? null,
            //new Utilisateur($objetFormatTableau["login"],$objetFormatTableau["nom"],$objetFormatTableau["prenom"],$objetFormatTableau["email"],$objetFormatTableau["mdp"])
            Utilisateur::construireDepuisTableau($objetFormatTableau),
        );
    }

    /**
     * Fonction permettant de récupérer l'utilisateur propriétaire du tableau
     * @return Utilisateur|null L'utilisateur propriétaire du tableau
     */
    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    /**
     * Fonction permettant de définir l'utilisateur propriétaire du tableau
     * @param Utilisateur $utilisateur L'utilisateur propriétaire du tableau
     * @return void
     */
    public function setUtilisateur(Utilisateur $utilisateur): void
    {
        $this->utilisateur = $utilisateur;
    }

    /**
     * Fonction permettant de récupérer l'id du tableau
     * @return int|null L'id du tableau
     */
    public function getIdTableau(): ?int
    {
        return $this->idTableau;
    }

    /**
     * Fonction permettant de définir l'id du tableau
     * @param int|null $idTableau L'id du tableau
     * @return void
     */
    public function setIdTableau(?int $idTableau): void
    {
        $this->idTableau = $idTableau;
    }

    /**
     * Fonction permettant de récupérer le titre du tableau
     * @return string|null Le titre du tableau
     */
    public function getTitreTableau(): ?string
    {
        return $this->titreTableau;
    }

    /**
     * Fonction permettant de définir le titre du tableau
     * @param string|null $titreTableau Le titre du tableau
     * @return void
     */
    public function setTitreTableau(?string $titreTableau): void
    {
        $this->titreTableau = $titreTableau;
    }

    /**
     * Fonction permettant de récupérer le code du tableau
     * @return string|null Le code du tableau
     */
    public function getCodeTableau(): ?string
    {
        return $this->codeTableau;
    }

    /**
     * Fonction permettant de définir le code du tableau
     * @param string|null $codeTableau Le code du tableau
     * @return void
     */
    public function setCodeTableau(?string $codeTableau): void
    {
        $this->codeTableau = $codeTableau;
    }

    /**
     * Fonction permettant de formater l'objet en tableau
     * @return array L'objet formaté en tableau
     */
    public function formatTableau(): array
    {
        return array(
            "idtableauTag" => $this->idTableau,
            "codetableauTag" => $this->codeTableau,
            "titretableauTag" => $this->titreTableau,
            "loginTag" => $this->utilisateur->getLogin(),
        );
    }


    public function jsonSerialize():mixed
    {
        return [
            "idTableau"=>$this->idTableau,
            "codeTableau"=>$this->codeTableau,
            "titreTableau"=>$this->titreTableau,
            "utilisateur"=>[
                "login"=>$this->utilisateur->getLogin(),
                "nom"=>$this->utilisateur->getNom(),
                "prenom"=>$this->utilisateur->getPrenom()
            ]
        ];
    }
}