<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;

interface AbstractRepositoryInterface
{
    /**
     * @return AbstractDataObject[]
     */
    public function recuperer(): array;

    /**
     * @param string $valeurClePrimaire
     * @return AbstractDataObject|null
     */
    public function recupererParClePrimaire(string $valeurClePrimaire): ?AbstractDataObject;

    /**
     * @param string $valeurClePrimaire
     * @return bool
     */
    public function supprimer(string $valeurClePrimaire): bool;

    /**
     * @param AbstractDataObject $object
     * @return void
     */
    public function mettreAJour(AbstractDataObject $object): void;

    /**
     * @param AbstractDataObject $object
     * @return bool
     */
    public function ajouter(AbstractDataObject $object): bool;
}