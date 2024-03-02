<?php
/** @var Colonne $colonne */
/** @var Colonne[] $colonnes */

use App\Trellotrolle\Modele\DataObject\Colonne;

$tableau = $colonne->getTableau();
$proprietaire = $tableau->getUtilisateur();
?>
<div>
    <form method="post" action="controleurFrontal.php">
        <fieldset>
            <h3>Création d'une carte :</h3>
            <p>
                <label for="titreCarte">Titre de la carte&#42;</label> :
                <input type="text" placeholder="Ma super tâche" name="titreCarte" id="titreCarte" minlength="1" maxlength="50" required>
            </p>
            <p>
                <label for="descriptifCarte">Description de la carte&#42;</label> :
                <div>
                    <textarea placeholder="Description de la tâche..." name="descriptifCarte" id="descriptifCarte" required></textarea>
                </div>
            </p>
            <p>
                <label for="couleurCarte">Couleur de la carte&#42;</label> :
                <input type="color" value="#FFFFFF" name="couleurCarte" id="couleurCarte" required>
            </p>
            <p>
                <label for="idColonne">Colonne de la carte&#42;</label> :
                <select name="idColonne" id="idColonne">
                    <?php foreach ($colonnes as $colonneOption) {?>
                        <option <?= $colonneOption->getIdColonne() === $colonne->getIdColonne() ? "selected" : "" ?> value="<?=$colonneOption->getIdColonne()?>"><?=htmlspecialchars($colonneOption->getTitreColonne())?></option>
                    <?php }?>
                </select>
            </p>
            <p>
                <label for="affectationsCarte">Membres affectés :</label>
                <div>
                    <select multiple name="affectationsCarte[]" id="affectationsCarte">
                        <option value="<?=htmlspecialchars($proprietaire->getLogin())?>"><?=htmlspecialchars($proprietaire->getPrenom())?> <?=htmlspecialchars($proprietaire->getNom())?> (<?=$proprietaire->getLogin()?>)</option>
                        <?php foreach ($tableau->getParticipants() as $membre) {?>
                            <option value="<?=htmlspecialchars($membre->getLogin())?>"><?=htmlspecialchars($membre->getPrenom())?> <?=htmlspecialchars($membre->getNom())?> (<?=$membre->getLogin()?>)</option>
                        <?php }?>
                    </select>
                </div>
            </p>
            <input type='hidden' name='action' value='creerCarte'>
            <input type='hidden' name='controleur' value='carte'>
            <p>
                <input type="submit" value="Créer la carte">
            </p>
        </fieldset>
    </form>
</div>