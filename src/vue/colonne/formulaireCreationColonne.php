<?php
/** @var int $idTableau */
?>
<div>
    <form method="post" action="controleurFrontal.php">
        <fieldset>
            <h3>Création d'une colonne :</h3>
            <p>
                <label for="nomColonne">Nom de la colonne&#42;</label> :
                <input type="text" placeholder="KO" name="nomColonne" id="nomColonne" minlength="1" maxlength="50" required>
            </p>
            <input type='hidden' name='idTableau' value='<?= htmlspecialchars($idTableau) ?>'>
            <input type='hidden' name='action' value='creerColonne'>
            <input type='hidden' name='controleur' value='colonne'>
            <p>
                <input type="submit" value="Créer la colonne">
            </p>
        </fieldset>
    </form>
</div>