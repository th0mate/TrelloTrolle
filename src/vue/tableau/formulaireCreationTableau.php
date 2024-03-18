<?php
use App\Trellotrolle\Lib\Conteneur;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\HttpFoundation\UrlHelper;

/** @var UrlGenerator $generateurUrl */
$generateurUrl = Conteneur::recupererService("generateurUrl");
/** @var UrlHelper $assistantUrl */
$assistantUrl = Conteneur::recupererService("assistantUrl");

?>

<div>
    <form method="post" action="<?= $generateurUrl->generate('creerTableau', ['controleur' => 'tableau'])?>"
        <fieldset>
            <h3>Création d'un tableau</h3>
            <p>
                <label for="nomColonne">Nom du tableau&#42;</label>
                <input type="text" placeholder="Mon super tableau" name="nomTableau" id="nomTableau" minlength="3" maxlength="50" required>
            </p>
            <input type='hidden' name='action' value='creerTableau'>
            <input type='hidden' name='controleur' value='tableau'>
            <p>
                <input type="submit" value="Créer le tableau">
            </p>
        </fieldset>
    </form>
</div>