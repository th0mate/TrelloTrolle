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
    <form method="post" action="<?= $generateurUrl->generate('recupererCompte', ['controleur' => 'utilisateur'])?>">
        <fieldset>
            <h3>Récupérer mon compte</h3>
            <p class="InputAddOn">
                <label class="InputAddOn-item" for="email_id">Email du compte&#42;</label>
                <input class="InputAddOn-field" type="email" value="" placeholder="rlebreton@yopmail.com" name="email" id="email_id" required>
            </p>
            <input type='hidden' name='action' value='recupererCompte'>
            <input type='hidden' name='controleur' value='utilisateur'>
            <p>
                <input class="InputAddOn-field" type="submit" value="Récupérer mon compte"/>
            </p>
        </fieldset>
    </form>
</div>
