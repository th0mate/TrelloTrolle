<?php
/** @var Carte $carte */
/** @var Colonne[] $colonnes */

use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\TableauRepository;

$loginsAffectes = array_map(function($u) {return $u->getLogin();}, CarteRepository::getAffectationsCarte($carte));
$colonneCarte = CarteRepository::getColonne($carte);
$tableau = ColonneRepository::getTableau($colonneCarte);
$proprietaire = TableauRepository::getUtilisateur($tableau);

use App\Trellotrolle\Lib\Conteneur;

use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\HttpFoundation\UrlHelper;

/** @var UrlGenerator $generateurUrl */
$generateurUrl = Conteneur::recupererService("generateurUrl");
/** @var UrlHelper $assistantUrl */
$assistantUrl = Conteneur::recupererService("assistantUrl");

?>
<div>
    <form method="post" action=<?= $generateurUrl->generate('mettreAJourCarte', ['controleur' => 'carte'])?>>
        <fieldset>
            <h3>Modification d'une carte :</h3>
            <p>
                <label for="titreCarte">Titre de la carte&#42;</label> :
                <input type="text" placeholder="Ma super tâche" name="titreCarte" id="titreCarte" value="<?= $carte->getTitreCarte() ?>" minlength="1" maxlength="50" required>
            </p>
            <p>
                <label for="descriptifCarte">Description de la carte&#42;</label> :
                <div>
                    <textarea placeholder="Description de la tâche..." name="descriptifCarte" id="descriptifCarte" required><?= $carte->getDescriptifCarte() ?></textarea>
                </div>
            </p>
            <p>
                <label for="couleurCarte">Couleur de la carte&#42;</label> :
                <input type="color" value="<?= $carte->getCouleurCarte() ?>" name="couleurCarte" id="couleurCarte" required>
            </p>
            <p>
                <label for="idColonne">Colonne de la carte&#42;</label> :
                <select name="idColonne" id="idColonne">
                    <?php foreach ($colonnes as $colonne) {?>
                        <option <?= $colonne->getIdColonne() === $colonneCarte->getIdColonne() ? "selected" : "" ?> value="<?=$colonne->getIdColonne()?>"><?=htmlspecialchars($colonne->getTitreColonne())?></option>
                    <?php }?>
                </select>
            </p>
            <p>
                <label for="affectationsCarte">Membres affectés :</label>
                <div>
                    <select multiple name="affectationsCarte[]" id="affectationsCarte">
                        <option <?= in_array($proprietaire->getLogin(), $loginsAffectes) ? "selected" : "" ?> value="<?=htmlspecialchars($proprietaire->getLogin())?>"><?=htmlspecialchars($proprietaire->getPrenom())?> <?=htmlspecialchars($proprietaire->getNom())?> (<?=$proprietaire->getLogin()?>)</option>
                        <?php foreach (TableauRepository::getParticipants($tableau) as $membre) {?>
                            <option <?= in_array($membre->getLogin(), $loginsAffectes) ? "selected" : "" ?> value="<?=htmlspecialchars($membre->getLogin())?>"><?=htmlspecialchars($membre->getPrenom())?> <?=htmlspecialchars($membre->getNom())?> (<?=$membre->getLogin()?>)</option>
                        <?php }?>
                    </select>
                </div>
            </p>
            <input type='hidden' name='idCarte' value='<?= htmlspecialchars($carte->getIdCarte()) ?>'>
            <input type='hidden' name='action' value='mettreAJourCarte'>
            <input type='hidden' name='controleur' value='carte'>
            <p>
                <input type="submit" value="Mettre à jour la carte">
            </p>
        </fieldset>
    </form>
</div>