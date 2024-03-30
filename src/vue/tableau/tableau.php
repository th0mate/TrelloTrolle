<?php
/** @var Tableau $tableau */
/** @var Colonne[] $colonnes */
/** @var Carte[][] $data */
/** @var array $participants */


use App\Trellotrolle\Lib\ConnexionUtilisateurSession;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Lib\Conteneur;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\HttpFoundation\UrlHelper;

/** @var UrlGenerator $generateurUrl */
$generateurUrl = Conteneur::recupererService("generateurUrl");
/** @var UrlHelper $assistantUrl */
$assistantUrl = Conteneur::recupererService("assistantUrl");

?>
<div class="trello-main">
    <aside>
        <div class="utilisateur icons_menu">
            <span><?= htmlspecialchars(TableauRepository::getUtilisateur($tableau)->getPrenom()) ?> <?= htmlspecialchars(TableauRepository::getUtilisateur($tableau)->getNom()) ?></span>
            <?php
                if(ConnexionUtilisateurSession::estConnecte() && TableauRepository::estProprietaire(ConnexionUtilisateurSession::getLoginUtilisateurConnecte(), $tableau)) {
            ?>
            <span><a href="<?= $generateurUrl->generate('afficherFormulaireMiseAJourTableau', ['controleur' => 'tableau', 'idTableau' => $tableau->getIdTableau()])?>">
                    <img class="icon" src="<?=$assistantUrl->getAbsoluteUrl('../ressources/img/editer.png');?>" alt="Éditer le tableau">
            <?php } ?>
        </div>
        <div class="tableau">
            <div class="icons_menu">
                <span><?=$tableau->getTitreTableau()?></span>
            </div>
            <div class="participants">
                Membres :
                <ul>
                    <li><?= htmlspecialchars(TableauRepository::getUtilisateur($tableau)->getPrenom()) ?> <?= htmlspecialchars(TableauRepository::getUtilisateur($tableau)->getNom()) ?></li>
                    <?php foreach (TableauRepository::getParticipants($tableau) as $participant) {?>
                        <li>
                            <div class="icons_menu_stick">
                                <?= $participant->getPrenom() ?> <?= $participant->getNom() ?>
                                <?php
                                if(ConnexionUtilisateurSession::estConnecte() && TableauRepository::estProprietaire(ConnexionUtilisateurSession::getLoginUtilisateurConnecte(), $tableau)) {
                                ?>
                                <span class="actions">
                                    <a href="<?= $generateurUrl->generate('supprimerMembre', ['controleur' => 'tableau', 'idTableau' => $tableau->getIdTableau(), 'login' => $participant->getLogin()])?>">
                                        <img class="icon" src="<?=$assistantUrl->getAbsoluteUrl('../ressources/img/x.png');?>" alt="Supprimer le membre">
                                </span>
                                <?php } ?>
                            </div>
                        </li>
                    <?php }?>
                    <?php
                    if(ConnexionUtilisateurSession::estConnecte() && TableauRepository::estProprietaire(ConnexionUtilisateurSession::getLoginUtilisateurConnecte(), $tableau)) {
                    ?>
                    <li><a href="<?= $generateurUrl->generate('afficherFormulaireAjoutMembre', ['controleur' => 'tableau', 'idTableau' => $tableau->getIdTableau()])?>">Ajouter un membre</a></li>
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
                if(ConnexionUtilisateurSession::estConnecte() && TableauRepository::estParticipantOuProprietaire(ConnexionUtilisateurSession::getLoginUtilisateurConnecte(), $tableau)) {
                    ?>
                    <span class="actions">
                            <a href="<?= $generateurUrl->generate('afficherFormulaireMiseAJourTableau', ['controleur' => 'tableau', 'idTableau' => $tableau->getIdTableau()])?>">
                                <img class="icon" src="<?=$assistantUrl->getAbsoluteUrl('../ressources/img/editer.png');?>" alt="Éditer le tableau">
                            </a>
                    </span>
                <?php } ?>
            </div>
            <div class="corps">
                <?php for ($i=0;$i<count($data);$i++) {?>
                <div class="colonne">
                    <div class="titre icons_menu">
                        <span><?= $colonnes[$i]->getTitreColonne() ?></span>
                        <?php
                            if(ConnexionUtilisateurSession::estConnecte() && TableauRepository::estParticipantOuProprietaire(ConnexionUtilisateurSession::getLoginUtilisateurConnecte(), $tableau)) {
                        ?>
                        <span class="actions">
                            <a href="<?= $generateurUrl->generate('afficherFormulaireMiseAJourColonne', ['controleur' => 'colonne', 'idColonne' => $colonnes[$i]->getIdColonne()])?>">
                                <img class="icon" src="<?=$assistantUrl->getAbsoluteUrl('../ressources/img/editer.png');?>" alt="Éditer la colonne">  </a>
                            <a href="<?= $generateurUrl->generate('supprimerColonne', ['controleur' => 'colonne', 'idColonne' => $colonnes[$i]->getIdColonne()])?>">
                                <img class="icon" src="<?=$assistantUrl->getAbsoluteUrl('../ressources/img/x.png');?>" alt="Supprimer la colonne"> </a>
                        </span>
                        <?php } ?>
                    </div>
                    <div class="corps">
                        <?php foreach ($data[$i] as $carte) {?>
                        <div id="c<?=$carte->getIdCarte()?>" class="carte" style="background-color: <?= $carte->getCouleurCarte() ?>">
                            <div class="titre icons_menu">
                                <span><?= htmlspecialchars($carte->getTitreCarte()) ?></span>
                                <?php
                                    if(ConnexionUtilisateurSession::estConnecte() && TableauRepository::estParticipantOuProprietaire(ConnexionUtilisateurSession::getLoginUtilisateurConnecte(), $tableau)) {
                                ?>
                                <span class="actions">
                                    <a href="<?= $generateurUrl->generate('afficherFormulaireMiseAJourCarte', ['controleur' => 'carte', 'idCarte' => $carte->getIdCarte()])?>">
                                        <img class="icon" src="<?=$assistantUrl->getAbsoluteUrl('../ressources/img/editer.png');?>" alt="Éditer la carte"> </a>
                                    <a href="<?= $generateurUrl->generate('supprimerCarte', ['controleur' => 'carte', 'idCarte' => $carte->getIdCarte()])?>">
                                        <img class="icon" src="<?=$assistantUrl->getAbsoluteUrl('../ressources/img/x.png');?>" alt="Supprimer la carte"> </a>
                                    <span data-onclick="tableauxDatas.supprimerCarte(<?= $carte->getIdCarte() ?>)"><img class="icon" src="../ressources/img/x.png" alt="Supprimer la carte"></span>
                                </span>
                                <?php } ?>
                            </div>
                            <div class="corps">
                                <?= htmlspecialchars($carte->getDescriptifCarte()) ?>
                            </div>
                            <div class="pied">
                                <?php foreach (CarteRepository::getAffectationsCarte($carte) as $utilisateur) {?>
                                    <span><?= ($utilisateur->getPrenom())[0] ?><?= ($utilisateur->getNom())[0] ?></span>
                                <?php }?>
                            </div>
                        </div>
                        <?php }?>
                        <?php
                            if(ConnexionUtilisateurSession::estConnecte() && TableauRepository::estParticipantOuProprietaire(ConnexionUtilisateurSession::getLoginUtilisateurConnecte(), $tableau)) {
                        ?>
                        <a class="ajout-tableau" href="<?= $generateurUrl->generate('afficherFormulaireCreationCarte', ['controleur' => 'carte', 'idColonne' => $colonnes[$i]->getIdColonne()])?>">
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
                    if(ConnexionUtilisateurSession::estConnecte() && TableauRepository::estParticipantOuProprietaire(ConnexionUtilisateurSession::getLoginUtilisateurConnecte(), $tableau)) {
                ?>
                    <a class="ajout-tableau" href="<?= $generateurUrl->generate('afficherFormulaireCreationColonne', ['controleur' => 'colonne', 'idTableau' => $tableau->getIdTableau()])?>">
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