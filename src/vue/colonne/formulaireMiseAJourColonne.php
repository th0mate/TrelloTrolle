<?php
/** @var int $idColonne */
/** @var string $nomColonne */
?>
<div>
    <form method="post" action="controleurFrontal.php">
        <fieldset>
            <h3>Modification d'une colonne :</h3>
            <p>
                <label for="nomColonne">Nom de la colonne&#42;</label> :
                <input type="text" placeholder="KO" name="nomColonne" id="nomColonne" minlength="1" maxlength="50" value='<?= htmlspecialchars($nomColonne) ?>' required>
            </p>
            <input type='hidden' name='idColonne' value='<?= htmlspecialchars($idColonne) ?>'>
            <input type='hidden' name='action' value='mettreAJourColonne'>
            <input type='hidden' name='controleur' value='colonne'>
            <p>
                <input type="submit" value="Modifier la colonne">
            </p>
        </fieldset>
    </form>
</div>