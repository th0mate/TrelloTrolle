<?php
/** @var Tableau $tableau */
/** @var Colonne[] $colonnes */
/** @var Carte[][] $data */
/** @var array $participants */


use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Tableau;

?>
<div class="trello-main">
    <aside>
        <div class="utilisateur icons_menu">
            <span><?= htmlspecialchars($tableau->getUtilisateur()->getPrenom()) ?> <?= htmlspecialchars($tableau->getUtilisateur()->getNom()) ?></span>
            <?php
                if(ConnexionUtilisateur::estConnecte() && $tableau->estProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
            ?>
            <span><a href="controleurFrontal.php?action=afficherFormulaireMiseAJour&controleur=utilisateur"><img class="icon" src="../ressources/img/editer.png" alt="Modifier l'utilisateur"></span></a>
            <?php } ?>
        </div>
        <div class="tableau">
            <div class="icons_menu">
                <span><?=$tableau->getTitreTableau()?></span>
            </div>
            <div class="participants">
                Membres :
                <ul>
                    <li><?= htmlspecialchars($tableau->getUtilisateur()->getPrenom()) ?> <?= htmlspecialchars($tableau->getUtilisateur()->getNom()) ?></li>
                    <?php foreach ($tableau->getParticipants() as $participant) {?>
                        <li>
                            <div class="icons_menu_stick">
                                <?= $participant->getPrenom() ?> <?= $participant->getNom() ?>
                                <?php
                                if(ConnexionUtilisateur::estConnecte() && $tableau->estProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
                                ?>
                                <span class="actions">
                                    <a href="controleurFrontal.php?action=supprimerMembre&controleur=tableau&idTableau=<?=$tableau->getIdTableau()?>&login=<?=rawurlencode($participant->getLogin())?>"><img class="icon" src="../ressources/img/x.png" alt="Retirer le membre"></a>
                                </span>
                                <?php } ?>
                            </div>
                        </li>
                    <?php }?>
                    <?php
                    if(ConnexionUtilisateur::estConnecte() && $tableau->estProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
                    ?>
                    <li><a href="controleurFrontal.php?action=afficherFormulaireAjoutMembre&controleur=tableau&idTableau=<?=$tableau->getIdTableau()?>">Ajouter un membre</a></li>
                    <?php } ?>
                </ul>
            </div>
            <div class="participants">
                Travail en cours :
                <ul>
                    <?php foreach ($participants as $participant) {?>
                    <li>
                        <div><?= htmlspecialchars($participant["infos"]->getPrenom()) ?> <?= htmlspecialchars($participant["infos"]->getNom()) ?></div>
                        <ul>
                            <?php foreach ($participant["colonnes"] as $colonne) {?>
                                <li><?= htmlspecialchars($colonne[1])?> <?= htmlspecialchars($colonne[0]) ?></li>
                            <?php }?>
                        </ul>
                    </li>
                    <?php }?>
                    <?php if(empty($participants)) {?>
                        <span><strong>Pas de travail en cours</strong></span>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </aside>
    <article>
        <div class="tableau">
            <div class="titre icons_menu">
                <?= $tableau->getTitreTableau() ?>
                <?php
                if(ConnexionUtilisateur::estConnecte() && $tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
                    ?>
                    <span class="actions">
                            <a href="controleurFrontal.php?action=afficherFormulaireMiseAJourTableau&controleur=tableau&idTableau=<?=$tableau->getIdTableau()?>"><img class="icon" src="../ressources/img/editer.png" alt="Éditer le tableau"></a>
                    </span>
                <?php } ?>
            </div>
            <div class="corps">
                <?php for ($i=0;$i<count($data);$i++) {?>
                <div class="colonne">
                    <div class="titre icons_menu">
                        <span><?= $colonnes[$i]->getTitreColonne() ?></span>
                        <?php
                            if(ConnexionUtilisateur::estConnecte() && $tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
                        ?>
                        <span class="actions">
                            <a href="controleurFrontal.php?action=afficherFormulaireMiseAJourColonne&controleur=colonne&idColonne=<?=$colonnes[$i]->getIdColonne()?>"><img class="icon" src="../ressources/img/editer.png" alt="Éditer la colonne"></a>
                            <a href="controleurFrontal.php?action=supprimerColonne&controleur=colonne&idColonne=<?= $colonnes[$i]->getIdColonne()?>"><img class="icon" src="../ressources/img/x.png" alt="Supprimer la colonne"></a>
                        </span>
                        <?php } ?>
                    </div>
                    <div class="corps">
                        <?php foreach ($data[$i] as $carte) {?>
                        <div id="c<?=$carte->getIdCarte()?>" class="carte" style="background-color: <?= $carte->getCouleurCarte() ?>">
                            <div class="titre icons_menu">
                                <span><?= htmlspecialchars($carte->getTitreCarte()) ?></span>
                                <?php
                                    if(ConnexionUtilisateur::estConnecte() && $tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
                                ?>
                                <span class="actions">
                                    <a href="controleurFrontal.php?action=afficherFormulaireMiseAJourCarte&controleur=carte&idCarte=<?=$carte->getIdCarte()?>"><img class="icon" src="../ressources/img/editer.png" alt="Éditer la carte"></a>
                                    <a href="controleurFrontal.php?action=supprimerCarte&controleur=carte&idCarte=<?= $carte->getIdCarte()?>"><img class="icon" src="../ressources/img/x.png" alt="Supprimer la carte"></a>
                                    <span data-onclick="tableauxDatas.supprimerCarte(<?= $carte->getIdCarte() ?>)"><img class="icon" src="../ressources/img/x.png" alt="Supprimer la carte"></span>
                                </span>
                                <?php } ?>
                            </div>
                            <div class="corps">
                                <?= htmlspecialchars($carte->getDescriptifCarte()) ?>
                            </div>
                            <div class="pied">
                                <?php foreach ($carte->getAffectationsCarte() as $utilisateur) {?>
                                    <span><?= ($utilisateur->getPrenom())[0] ?><?= ($utilisateur->getNom())[0] ?></span>
                                <?php }?>
                            </div>
                        </div>
                        <?php }?>
                        <?php
                            if(ConnexionUtilisateur::estConnecte() && $tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
                        ?>
                        <a class="ajout-tableau" href="controleurFrontal.php?action=afficherFormulaireCreationCarte&controleur=carte&idColonne=<?=$colonnes[$i]->getIdColonne()?>">
                            <div>
                                <div class="titre icons_menu btn-ajout">
                                    <span>Ajouter une carte</span>
                                </div>
                            </div>
                        </a>
                        <?php } ?>
                    </div>
                </div>
                <?php }?>
                <?php
                    if(ConnexionUtilisateur::estConnecte() && $tableau->estParticipantOuProprietaire(ConnexionUtilisateur::getLoginUtilisateurConnecte())) {
                ?>
                    <a class="ajout-tableau" href="controleurFrontal.php?action=afficherFormulaireCreationColonne&controleur=colonne&idTableau=<?=$tableau->getIdTableau()?>">
                        <div class="colonne">
                            <div class="titre icons_menu btn-ajout">
                                <span>Ajouter une colonne</span>
                            </div>
                        </div>
                    </a>
                    <?php } ?>
            </div>
        </div>
    </article>
</div>