<?php
/** @var Tableau[] $tableaux */

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Lib\Conteneur;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\HttpFoundation\UrlHelper;

/** @var UrlGenerator $generateurUrl */
$generateurUrl = Conteneur::recupererService("generateurUrl");
/** @var UrlHelper $assistantUrl */
$assistantUrl = Conteneur::recupererService("assistantUrl");

?>

<div>
    <h3>Mes tableaux (<?= count($tableaux)  ?>)</h3>
    <div class="tableaux">
        <?php foreach ($tableaux as $tableau) { ?>
            <div class="ligne_tableau">
                <div><?= htmlspecialchars($tableau->getTitreTableau()) ?></div>
                <div>
                    <a href="<?=$generateurUrl->generate('afficherTableau', ['codeTableau' => $tableau->getCodeTableau()])?>">
                        Modifier
                    </a>
                </div>
                <div>
                    <?php
                    if ($tableau->estProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
                        ?>
                        <a href="<?=$generateurUrl->generate('supprimerTableau', ['idTableau' => $tableau->getIdTableau()])?>">Supprimer
                            le tableau</a>
                    <?php } else { ?>
                        <a href="<?=$generateurUrl->generate('quitterTableau', ['idTableau' => $tableau->getIdTableau()])?>">Quitter
                            le tableau</a>
                    <?php } ?>
                </div>
            </div>


        <?php } ?>

    </div>
    <div>
        <a href='<?=$generateurUrl->generate('afficherFormulaireCreationTableau')?>'>Ajouter un tableau</a>
    </div>
</div>
