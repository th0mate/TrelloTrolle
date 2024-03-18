<?php
/** @var $tableau Tableau */
/** @var array $utilisateurs */

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
    <form method="post" action="<?= $generateurUrl->generate('ajouterMembreTableau', ['controleur' => 'tableau'])?>">
        <fieldset>
            <h3>Ajout d'un membre au tableau <?= $tableau->getTitreTableau() ?>:</h3>
            <p>
                <label for="login">Membre à ajouter&#42;</label> :
                <select name="login" id="login">
                    <?php foreach ($utilisateurs as $utilisateur) {?>
                        <option value="<?=$utilisateur->getLogin()?>"><?=$utilisateur->getPrenom()?> <?=$utilisateur->getNom()?> (<?=$utilisateur->getLogin()?>)</option>
                    <?php }?>
                </select>
            </p>
            <input type='hidden' name='idTableau' value='<?= htmlspecialchars($tableau->getIdTableau()) ?>'>
            <input type='hidden' name='action' value='ajouterMembre'>
            <input type='hidden' name='controleur' value='tableau'>
            <p>
                <input type="submit" value="Ajouter">
            </p>
        </fieldset>
    </form>
</div>