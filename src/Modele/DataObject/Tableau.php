<?php

namespace App\Trellotrolle\Modele\DataObject;

use App\Trellotrolle\Modele\Repository\TableauRepository;

class Tableau extends AbstractDataObject
{
    /**
     * @param int|null $idTableau
     * @param string|null $codeTableau
     * @param string|null $titreTableau
     * @param Utilisateur|null $utilisateur
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
     * @param array $objetFormatTableau
     * @return Tableau
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
     * @return Utilisateur|null
     */
    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    /**
     * @param Utilisateur $utilisateur
     * @return void
     */
    public function setUtilisateur(Utilisateur $utilisateur): void
    {
        $this->utilisateur = $utilisateur;
    }

    /**
     * @return int|null
     */
    public function getIdTableau(): ?int
    {
        return $this->idTableau;
    }

    /**
     * @param int|null $idTableau
     * @return void
     */
    public function setIdTableau(?int $idTableau): void
    {
        $this->idTableau = $idTableau;
    }

    /**
     * @return string|null
     */
    public function getTitreTableau(): ?string
    {
        return $this->titreTableau;
    }

    /**
     * @param string|null $titreTableau
     * @return void
     */
    public function setTitreTableau(?string $titreTableau): void
    {
        $this->titreTableau = $titreTableau;
    }

    /**
     * @return string|null
     */
    public function getCodeTableau(): ?string
    {
        return $this->codeTableau;
    }

    /**
     * @param string|null $codeTableau
     * @return void
     */
    public function setCodeTableau(?string $codeTableau): void
    {
        $this->codeTableau = $codeTableau;
    }

    /**
     * @return array
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


}