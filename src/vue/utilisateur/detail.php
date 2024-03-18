<?php
/** @var Utilisateur $utilisateur */

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Lib\Conteneur;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\HttpFoundation\UrlHelper;

/** @var UrlGenerator $generateurUrl */
$generateurUrl = Conteneur::recupererService("generateurUrl");
/** @var UrlHelper $assistantUrl */
$assistantUrl = Conteneur::recupererService("assistantUrl");

$loginHTML = htmlspecialchars($utilisateur->getLogin());
$loginURL = rawurlencode($utilisateur->getLogin());
$prenomHTML = htmlspecialchars($utilisateur->getPrenom());
$nomHTML = htmlspecialchars($utilisateur->getNom());

?>
<p>
    
    <form>
    <h3>Détail du compte <?=$loginHTML?></h3>
    <p>
    
<label for="">Nom</label>
    <input type="text" disabled value="<?=$nomHTML?>">
    <label for="">Prénom</label>
    <input type="text" disabled value="<?=$prenomHTML?>">
</p>    
    
   
    

    



<?php if(ConnexionUtilisateur::estUtilisateur($utilisateur->getLogin())) { ?>
        <div class="btn btn_maj"><a href="<?= $generateurUrl->generate('afficherFormulaireMiseAJourUtilisateur', ['controleur' => 'utilisateur', 'login' => $loginURL])?>">Mettre
            à jour le compte</a></div>
        <div class="btn btn_suppr"><a href="<?= $generateurUrl->generate('supprimer', ['controleur' => 'utilisateur', 'login' => $loginURL])?>">Supprimer le compte</a></div>
    <?php } ?>
</form>
</p>