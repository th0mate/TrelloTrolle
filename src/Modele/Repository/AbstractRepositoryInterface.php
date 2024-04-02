<?php

namespace App\Trellotrolle\Modele\Repository;

use App\Trellotrolle\Modele\DataObject\AbstractDataObject;

interface AbstractRepositoryInterface
{
    /**
     * fonction permettant de récupérer tous les objets
     * @return AbstractDataObject[] Les objets récupérés
     */
    public function recuperer(): array;

    /**
     * Fonction permettant de récupérer un objet en fonction de la clé primaire
     * @param string $valeurClePrimaire La valeur de la clé primaire
     * @return AbstractDataObject|null L'objet récupéré
     */
    public function recupererParClePrimaire(string $valeurClePrimaire): ?AbstractDataObject;

    /**
     * Fonction permettant de supprimer un objet en fonction de la clé primaire
     * @param string $valeurClePrimaire La valeur de la clé primaire
     * @return bool Vrai si la suppression a réussi, faux sinon
     */
    public function supprimer(string $valeurClePrimaire): bool;

    /**
     * Fonction permettant de mettre à jour un objet
     * @param AbstractDataObject $object L'objet à mettre à jour
     * @return void
     */
    public function mettreAJour(AbstractDataObject $object): void;

    /**
     * Fonction permettant d'ajouter un objet
     * @param AbstractDataObject $object L'objet à ajouter
     * @return bool Vrai si l'ajout a réussi, faux sinon
     */
    public function ajouter(AbstractDataObject $object): bool;
}