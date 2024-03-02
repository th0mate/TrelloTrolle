<?php

namespace App\Trellotrolle\Modele\DataObject;

class Colonne extends AbstractDataObject
{
    public function __construct(
        private Tableau $tableau,
        private int $idColonne,
        private string $titreColonne
    )
    {}

    public static function construireDepuisTableau(array $objetFormatTableau) : Colonne {
        return new Colonne(
            Tableau::construireDepuisTableau($objetFormatTableau),
            $objetFormatTableau["idcolonne"],
            $objetFormatTableau["titrecolonne"],
        );
    }

    public function getTableau(): Tableau
    {
        return $this->tableau;
    }

    public function setTableau(Tableau $tableau): void
    {
        $this->tableau = $tableau;
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
        return array_merge(
            $this->tableau->formatTableau(),
            array(
                "idcolonneTag" => $this->idColonne,
                "titrecolonneTag" => $this->titreColonne,
            ),
        );
    }
}