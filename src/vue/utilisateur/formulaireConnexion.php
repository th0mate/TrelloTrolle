<?php
use App\Trellotrolle\Modele\HTTP\Cookie;
?>
<div>
    <form method="post" action="controleurFrontal.php">
        <fieldset>
            <h3>Connexion</h3>
            <p >
                <label  for="login_id">Login</label>
                <input  type="text" value="<?= Cookie::contient("login") ? Cookie::lire("login") : ""?>" placeholder="Ex : rlebreton" name="login" id="login_id" required>
            </p>
            <p >
                <label  for="mdp_id">Mot de passe</label>
                <input  type="password" value="<?= Cookie::contient("mdp") ? Cookie::lire("mdp") : ""?>" placeholder="" name="mdp" id="mdp_id" required>
            </p>
            <a href="controleurFrontal.php?action=afficherFormulaireRecuperationCompte&controleur=utilisateur">Login et/ou mot de passe oubliés ?</a>
            <input type='hidden' name='action' value='connecter'>
            <input type='hidden' name='controleur' value='utilisateur'>
            <p>
                <input  type="submit" value="Se connecter"/>
            </p>
        </fieldset>
    </form>
</div>