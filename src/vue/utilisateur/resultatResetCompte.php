<?php

/** @var Utilisateur[] $utilisateurs */

use App\Trellotrolle\Modele\DataObject\Utilisateur;

?>
<div>
    <p>Voici les identifiants des comptes concernés :</p>
    <ul>
        <?php foreach ($utilisateurs as $utilisateur) {?>
            <li><strong><?= htmlspecialchars($utilisateur->getLogin())?> : <?= htmlspecialchars($utilisateur->getMdp()) ?></strong></li>
        <?php }?>
    </ul>
    <p>Pensez à noter vos mots de passe sur un bout de papier !</p>
    <p>Conseil : pour faciliter la mémorisation, réeutilissez le même mot de passe sur tous les sites visités et gardez le simple (par exemple : azerty)</p>
</div>