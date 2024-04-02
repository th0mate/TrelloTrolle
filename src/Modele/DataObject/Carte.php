<?php

namespace App\Trellotrolle\Modele\DataObject;

class Carte extends AbstractDataObject implements \JsonSerializable
{

    /**
     * Carte constructor.
     * @param int $idCarte L'id de la carte
     * @param string $titreCarte Le titre de la carte
     * @param string $descriptifCarte Le descriptif de la carte
     * @param string $couleurCarte La couleur de la carte
     * @param Colonne $colonne La colonne où se trouve la carte
     */
    public function __construct(
        private int     $idCarte,
        private string  $titreCarte,
        private string  $descriptifCarte,
        private string  $couleurCarte,
        private Colonne $colonne,
    )
    {
    }

    /**
     * Fonction permettant de construire un objet depuis un tableau de paramètres
     * @param array $objetFormatTableau Le tableau de paramètres
     * @return Carte L'objet construit
     */
    public static function construireDepuisTableau(array $objetFormatTableau): Carte
    {
        return new Carte(
            $objetFormatTableau["idcarte"],
            $objetFormatTableau["titrecarte"],
            $objetFormatTableau["descriptifcarte"],
            $objetFormatTableau["couleurcarte"],
            Colonne::construireDepuisTableau($objetFormatTableau),
        );
    }

    /**
     * Fonction permettant de récupérer la colonne de la carte
     * @return Colonne La colonne
     */
    public function getColonne(): Colonne
    {
        return $this->colonne;
    }

    /**
     * Fonction permettant de définir la colonne de la carte
     * @param Colonne $colonne La colonne de la carte
     * @return void
     */
    public function setColonne(Colonne $colonne): void
    {
        $this->colonne = $colonne;
    }

    /**
     * Fonction permettant de récupérer l'id de la carte
     * @return int|null L'id de la carte
     */
    public function getIdCarte(): ?int
    {
        return $this->idCarte;
    }

    /**
     * Fonction permettant de définir l'id de la carte
     * @param int|null $idCarte L'id de la carte
     * @return void
     */
    public function setIdCarte(?int $idCarte): void
    {
        $this->idCarte = $idCarte;
    }

    /**
     * Fonction permettant de récupérer le titre de la carte
     * @return string|null Le titre de la carte
     */
    public function getTitreCarte(): ?string
    {
        return $this->titreCarte;
    }

    /**
     * Fonction permettant de définir le titre de la carte
     * @param string|null $titreCarte Le titre de la carte
     * @return void
     */
    public function setTitreCarte(?string $titreCarte): void
    {
        $this->titreCarte = $titreCarte;
    }

    /**
     * Fonction permettant de récupérer le descriptif de la carte
     * @return string|null Le descriptif de la carte
     */
    public function getDescriptifCarte(): ?string
    {
        return $this->descriptifCarte;
    }

    /**
     * Fonction permettant de définir le descriptif de la carte
     * @param string|null $descriptifCarte Le descriptif de la carte
     * @return void
     */
    public function setDescriptifCarte(?string $descriptifCarte): void
    {
        $this->descriptifCarte = $descriptifCarte;
    }

    /**
     * Fonction permettant de récupérer la couleur de la carte
     * @return string|null La couleur de la carte
     */
    public function getCouleurCarte(): ?string
    {
        return $this->couleurCarte;
    }

    /**
     * Fonction permettant de définir la couleur de la carte
     * @param string|null $couleurCarte La couleur de la carte
     * @return void
     */
    public function setCouleurCarte(?string $couleurCarte): void
    {
        $this->couleurCarte = $couleurCarte;
    }

    /**
     * Fonction permettant de formater un objet en tableau
     * @return array L'objet formaté en tableau
     */
    public function formatTableau(): array
    {
        return array(
            "idcarteTag" => $this->idCarte,
            "titrecarteTag" => $this->titreCarte,
            "descriptifcarteTag" => $this->descriptifCarte,
            "couleurcarteTag" => $this->couleurCarte,
            "idcolonneTag" => $this->colonne->getIdColonne(),
        );
    }


    /**
     * Fonction permettant de sérialiser un objet en JSON
     * @return mixed L'objet sérialisé
     */
    public function jsonSerialize(): mixed
    {
        return [
            "idCarte"=> $this->getIdCarte(),
            "titreCarte"=> $this->getTitreCarte(),
            "descriptifCarte"=> $this->getDescriptifCarte(),
            "couleurCarte"=> $this->getCouleurCarte(),
            "colonne"=> [
                "idColonne"=> $this->getColonne()->getIdColonne(),
                "titreColonne"=> $this->getColonne()->getTitreColonne(),
            ]
        ];
    }
}