<?php

namespace App\Trellotrolle\Modele\DataObject;

class Colonne extends AbstractDataObject implements \JsonSerializable
{
    /**
     * Colonne constructor.
     * @param int|null $idColonne L'id de la colonne
     * @param string|null $titreColonne Le titre de la colonne
     * @param Tableau|null $tableau Le tableau où se trouve la colonne
     */
    public function __construct(
        private ?int     $idColonne,
        private ?string  $titreColonne,
        private ?Tableau $tableau,
        private ?int $ordre,
    )
    {
    }

    /***
     * Fonction permettant de construire un objet depuis un tableau de paramètres
     * @param array $objetFormatTableau Le tableau de paramètres
     * @return Colonne L'objet construit
     */
    public static function construireDepuisTableau(array $objetFormatTableau): Colonne
    {
        return new Colonne(
            $objetFormatTableau["idcolonne"] ?? null,
            $objetFormatTableau["titrecolonne"] ?? null,
            Tableau::construireDepuisTableau($objetFormatTableau),
            $objetFormatTableau["ordre"] ?? null,
        );
    }

    /**
     * Fonction permettant de récupérer le tableau de la colonne
     * @return Tableau Le tableau
     */
    public function getTableau(): Tableau
    {
        return $this->tableau;
    }

    /**
     * Fonction permettant de définir le tableau de la colonne
     * @param Tableau $tableau Le tableau de la colonne
     * @return void
     */
    public function setTableau(Tableau $tableau): void
    {
        $this->tableau = $tableau;
    }


    /**
     * Fonction permettant de récupérer l'id de la colonne
     * @return int|null L'id de la colonne
     */
    public function getIdColonne(): ?int
    {
        return $this->idColonne;
    }

    /**
     * Fonction permettant de définir l'id de la colonne
     * @param int|null $idColonne L'id de la colonne
     * @return void
     */
    public function setIdColonne(?int $idColonne): void
    {
        $this->idColonne = $idColonne;
    }

    /**
     * Fonction permettant de récupérer le titre de la colonne
     * @return string|null Le titre de la colonne
     */
    public function getTitreColonne(): ?string
    {
        return $this->titreColonne;
    }

    /**
     * Fonction permettant de définir le titre de la colonne
     * @param string|null $titreColonne Le titre de la colonne
     * @return void
     */
    public function setTitreColonne(?string $titreColonne): void
    {
        $this->titreColonne = $titreColonne;
    }

    /**
     * Fonction permettant de récupérer l'ordre de la colonne
     * (sa position dans le tableau)
     * @return int|null L'ordre de la colonne
     */
    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    /**
     * Fonction permettant de définir l'ordre de la colonne
     * @param int|null $ordre L'ordre de la colonne
     * @return void
     */
    public function setOrdre(?int $ordre): void
    {
        $this->ordre = $ordre;
    }

    /**
     * Fonction permettant de formater l'objet en tableau
     * @return array L'objet formaté en tableau
     */
    public function formatTableau(): array
    {
        return array(
            "idcolonneTag" => $this->idColonne,
            "titrecolonneTag" => $this->titreColonne,
            "idtableauTag" => $this->tableau->getIdTableau(),
            "ordreTag" => $this->ordre,
        );
    }

    /**
     * Fonction permettant de sérialiser l'objet en JSON
     * @return mixed L'objet sérialisé
     */
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