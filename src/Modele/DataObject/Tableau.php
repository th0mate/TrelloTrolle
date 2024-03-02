<?php

namespace App\Trellotrolle\Modele\DataObject;

class Tableau extends AbstractDataObject
{
    public function __construct(
        private Utilisateur $utilisateur,
        private int $idTableau,
        private string $codeTableau,
        private string $titreTableau,
        private array $participants,
    )
    {}

    public static function construireDepuisTableau(array $objetFormatTableau) : Tableau {
        return new Tableau(
            Utilisateur::construireDepuisTableau($objetFormatTableau),
            $objetFormatTableau["idtableau"],
            $objetFormatTableau["codetableau"],
            $objetFormatTableau["titretableau"],
            Utilisateur::construireUtilisateursDepuisJson($objetFormatTableau["participants"])
        );
    }

    public function getUtilisateur(): Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(Utilisateur $utilisateur): void
    {
        $this->utilisateur = $utilisateur;
    }

    public function getIdTableau(): ?int
    {
        return $this->idTableau;
    }

    public function setIdTableau(?int $idTableau): void
    {
        $this->idTableau = $idTableau;
    }

    public function getTitreTableau(): ?string
    {
        return $this->titreTableau;
    }

    public function setTitreTableau(?string $titreTableau): void
    {
        $this->titreTableau = $titreTableau;
    }

    public function getCodeTableau(): ?string
    {
        return $this->codeTableau;
    }

    public function setCodeTableau(?string $codeTableau): void
    {
        $this->codeTableau = $codeTableau;
    }

    public function getParticipants(): ?array
    {
        return $this->participants;
    }

    public function setParticipants(?array $participants): void
    {
        $this->participants = $participants;
    }

    public function estProprietaire(string $login): bool {
        return $this->utilisateur->getLogin() === $login;
    }

    public function estParticipant(string $login) : bool{
        foreach ($this->participants as $participant) {
            if($participant->getLogin() === $login) {
                return true;
            }
        }
        return false;
    }

    public function estParticipantOuProprietaire(string $login) : bool{
        return $this->estProprietaire($login) || $this->estParticipant($login);
    }

    public function formatTableau(): array
    {
        return array_merge(
            $this->utilisateur->formatTableau(),
            array(
                "idtableauTag" => $this->idTableau,
                "codetableauTag" => $this->codeTableau,
                "titretableauTag" => $this->titreTableau,
                "participantsTag" => Utilisateur::formatJsonListeUtilisateurs($this->participants)
            ),
        );
    }
}