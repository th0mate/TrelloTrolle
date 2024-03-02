<?php
/** @var int $idTableau */
/** @var string $nomTableau */
?>
<div>
    <form method="post" action="controleurFrontal.php">
        <fieldset>
            <h3>Modification d'un tableau :</h3>
            <p>
                <label for="nomTableau">Nom du tableau&#42;</label> :
                <input type="text" placeholder="Mon super tableau" name="nomTableau" id="nomTableau" minlength="3" maxlength="50" value='<?= htmlspecialchars($nomTableau) ?>' required>
            </p>
            <input type='hidden' name='idTableau' value='<?= htmlspecialchars($idTableau) ?>'>
            <input type='hidden' name='action' value='mettreAJourTableau'>
            <input type='hidden' name='controleur' value='tableau'>
            <p>
                <input type="submit" value="Modifier le tableau">
            </p>
        </fieldset>
    </form>
</div>