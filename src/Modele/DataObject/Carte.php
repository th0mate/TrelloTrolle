<?php

namespace App\Trellotrolle\Modele\DataObject;

class Carte extends AbstractDataObject implements \JsonSerializable
{

    /**
     * @param int $idCarte
     * @param string $titreCarte
     * @param string $descriptifCarte
     * @param string $couleurCarte
     * @param Colonne $colonne
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
     * @param array $objetFormatTableau
     * @return Carte
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
     * @return Colonne
     */
    public function getColonne(): Colonne
    {
        return $this->colonne;
    }

    /**
     * @param Colonne $colonne
     * @return void
     */
    public function setColonne(Colonne $colonne): void
    {
        $this->colonne = $colonne;
    }

    /**
     * @return int|null
     */
    public function getIdCarte(): ?int
    {
        return $this->idCarte;
    }

    /**
     * @param int|null $idCarte
     * @return void
     */
    public function setIdCarte(?int $idCarte): void
    {
        $this->idCarte = $idCarte;
    }

    /**
     * @return string|null
     */
    public function getTitreCarte(): ?string
    {
        return $this->titreCarte;
    }

    /**
     * @param string|null $titreCarte
     * @return void
     */
    public function setTitreCarte(?string $titreCarte): void
    {
        $this->titreCarte = $titreCarte;
    }

    /**
     * @return string|null
     */
    public function getDescriptifCarte(): ?string
    {
        return $this->descriptifCarte;
    }

    /**
     * @param string|null $descriptifCarte
     * @return void
     */
    public function setDescriptifCarte(?string $descriptifCarte): void
    {
        $this->descriptifCarte = $descriptifCarte;
    }

    /**
     * @return string|null
     */
    public function getCouleurCarte(): ?string
    {
        return $this->couleurCarte;
    }

    /**
     * @param string|null $couleurCarte
     * @return void
     */
    public function setCouleurCarte(?string $couleurCarte): void
    {
        $this->couleurCarte = $couleurCarte;
    }

    /**
     * @return array
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
     * @return mixed
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