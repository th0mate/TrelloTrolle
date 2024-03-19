<?php
/** @var Tableau[] $tableaux */

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\Repository\TableauRepository;

?>

<div>
    <h3>Mes tableaux (<?= count($tableaux)  ?>)</h3>
    <div class="tableaux">
        <?php foreach ($tableaux as $tableau) { ?>
            <div class="ligne_tableau">
                <div><?= htmlspecialchars($tableau->getTitreTableau()) ?></div>
                <div>
                    <a href="controleurFrontal.php?action=afficherTableau&controleur=tableau&codeTableau=<?= rawurlencode($tableau->getCodeTableau()) ?>">
                        Modifier
                    </a>
                </div>
                <div>
                    <?php
                    if (TableauRepository::estProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte(), $tableau)) {
                        ?>
                        <a href="controleurFrontal.php?action=supprimerTableau&controleur=tableau&idTableau=<?= $tableau->getIdTableau() ?>">Supprimer
                            le tableau</a>
                    <?php } else { ?>
                        <a href="controleurFrontal.php?action=quitterTableau&controleur=tableau&idTableau=<?= $tableau->getIdTableau() ?>">Quitter
                            le tableau</a>
                    <?php } ?>
                </div>
            </div>


        <?php } ?>

    </div>
    <div>
        <a href='?action=afficherFormulaireCreationTableau&controleur=tableau'>Ajouter un tableau</a>
    </div>
</div>
