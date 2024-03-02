<?php
/** @var Utilisateur $utilisateur */

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Modele\DataObject\Utilisateur;

$loginHTML = htmlspecialchars($utilisateur->getLogin());
$loginURL = rawurlencode($utilisateur->getLogin());
$prenomHTML = htmlspecialchars($utilisateur->getPrenom());
$nomHTML = htmlspecialchars($utilisateur->getNom());

echo <<< HTML
<p>
    
    <form>
    <h3>Détail du compte $loginHTML</h3>
    <p>
    
<label for="">Nom</label>
    <input type="text" disabled value="$nomHTML">
    <label for="">Prénom</label>
    <input type="text" disabled value="$prenomHTML">
</p>    
    
   
    

    
   
HTML;

if (
    ConnexionUtilisateur::estUtilisateur($utilisateur->getLogin())
) {
    echo <<< HTML
        <div class="btn btn_maj"><a href="controleurFrontal.php?action=afficherFormulaireMiseAJour&controleur=utilisateur">Mettre
            à jour le compte</a></div>
        <div class="btn btn_suppr"><a href="controleurFrontal.php?action=supprimer&controleur=utilisateur&login={$loginURL}">Supprimer le compte</a></div>
HTML;
}
echo "</form></p>";