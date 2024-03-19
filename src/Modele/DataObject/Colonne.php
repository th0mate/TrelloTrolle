<?php

namespace App\Trellotrolle\Modele\DataObject;

class Colonne extends AbstractDataObject
{
    public function __construct(
        private int $idColonne,
        private string $titreColonne,
        private int $idTableau
    )
    {}

    public static function construireDepuisTableau(array $objetFormatTableau) : Colonne {
        return new Colonne(
            $objetFormatTableau["idcolonne"],
            $objetFormatTableau["titrecolonne"],
            $objetFormatTableau["idtableau"],
        );
    }

    public function getIdTableau(): int
    {
        return $this->idTableau;
    }

    public function setIdTableau(int $idTableau): void
    {
        $this->idTableau = $idTableau;
    }

    public function getIdColonne(): ?int
    {
        return $this->idColonne;
    }

    public function setIdColonne(?int $idColonne): void
    {
        $this->idColonne = $idColonne;
    }

    public function getTitreColonne(): ?string
    {
        return $this->titreColonne;
    }

    public function setTitreColonne(?string $titreColonne): void
    {
        $this->titreColonne = $titreColonne;
    }

    public function formatTableau(): array
    {
        return array(
                "idcolonneTag" => $this->idColonne,
                "titrecolonneTag" => $this->titreColonne,
                "idtableauTag" => $this->idTableau,
        );
    }
}