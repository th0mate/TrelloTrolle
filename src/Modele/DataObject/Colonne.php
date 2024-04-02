<?php

namespace App\Trellotrolle\Modele\DataObject;

class Colonne extends AbstractDataObject implements \JsonSerializable
{
    /**
     * @param int|null $idColonne
     * @param string|null $titreColonne
     * @param Tableau|null $tableau
     */
    public function __construct(
        private ?int     $idColonne,
        private ?string  $titreColonne,
        private ?Tableau $tableau,
        private ?int $ordre,
    )
    {
    }

    public static function construireDepuisTableau(array $objetFormatTableau): Colonne
    {
        return new Colonne(
            $objetFormatTableau["idcolonne"] ?? null,
            $objetFormatTableau["titrecolonne"] ?? null,
            Tableau::construireDepuisTableau($objetFormatTableau),
            $objetFormatTableau["ordre"] ?? null,
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

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(?int $ordre): void
    {
        $this->ordre = $ordre;
    }

    public function formatTableau(): array
    {
        return array(
            "idcolonneTag" => $this->idColonne,
            "titrecolonneTag" => $this->titreColonne,
            "idtableauTag" => $this->tableau->getIdTableau(),
            "ordreTag" => $this->ordre,
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            "idcolonne" => $this->idColonne,
            "titrecolonne" => $this->titreColonne,
            "tableau" =>[
                "idtableau" => $this->tableau->getIdTableau(),
                "titretableau" => $this->tableau->getTitreTableau(),
            ]
        ];
    }
}