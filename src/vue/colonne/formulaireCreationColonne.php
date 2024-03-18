<?php
/** @var int $idTableau */

use App\Trellotrolle\Lib\Conteneur;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\HttpFoundation\UrlHelper;

/** @var UrlGenerator $generateurUrl */
$generateurUrl = Conteneur::recupererService("generateurUrl");
/** @var UrlHelper $assistantUrl */
$assistantUrl = Conteneur::recupererService("assistantUrl");
?>
<div>
    <form method="post" action=<?= $generateurUrl->generate('creerColonne', ['controleur' => 'colonne'])?>>
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