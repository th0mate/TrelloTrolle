<?php
/** @var int $idTableau */
/** @var string $nomTableau */
use App\Trellotrolle\Lib\Conteneur;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\HttpFoundation\UrlHelper;

/** @var UrlGenerator $generateurUrl */
$generateurUrl = Conteneur::recupererService("generateurUrl");
/** @var UrlHelper $assistantUrl */
$assistantUrl = Conteneur::recupererService("assistantUrl");

?>
<div>
    <form method="post" action="<?= $generateurUrl->generate('mettreAJourTableau', ['controleur' => 'tableau'])?>">
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